<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Hostel;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsslController extends Controller
{
    private const NS = 'http://tempuri.org/';

    private string $baseUrl;
    private array  $auth;
    private int    $connectTimeout;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl        = rtrim((string) config('services.essl.url'), '?');
        $this->connectTimeout = (int) config('services.essl.connect_timeout', 5);
        $this->timeout        = (int) config('services.essl.timeout', 30);

        $this->auth = [
            'api_key'  => (string) config('services.essl.api_key'),
            'username' => (string) config('services.essl.username'),
            'password' => (string) config('services.essl.password'),
        ];
    }

    /* =========================================================
     |  CORE: SOAP CALL
     ========================================================= */

    private function soapCall(string $method, array $params = [], ?int $timeout = null): Response
    {
        if (config('services.essl.mock')) {
            return $this->mockResponse($method);
        }

        $inner = '';
        foreach ($params as $name => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $safe = is_int($value) || is_float($value)
                ? (string) $value
                : htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $inner .= "<{$name}>{$safe}</{$name}>";
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
            . ' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            . '<soap:Body>'
            . "<{$method} xmlns=\"" . self::NS . "\">{$inner}</{$method}>"
            . '</soap:Body>'
            . '</soap:Envelope>';

        Log::debug('eSSL SOAP Request', [
            'url'    => $this->baseUrl,
            'method' => $method,
            'xml'    => $xml,
        ]);

        return Http::connectTimeout($this->connectTimeout)
            ->timeout($timeout ?? $this->timeout)
            ->withHeaders([
                'SOAPAction' => '"' . self::NS . $method . '"',
                'Content-Type' => 'text/xml; charset=utf-8',
            ])
            ->withBody($xml, 'text/xml; charset=utf-8')
            ->post($this->baseUrl);
    }

    private function mockResponse(string $method): Response
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>'
            . "<{$method}Response xmlns=\"" . self::NS . "\">"
            . "<{$method}Result>Success (mock)</{$method}Result>"
            . '<CommandId>0</CommandId>'
            . "</{$method}Response></soap:Body></soap:Envelope>";

        return new Response(
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'text/xml'], $xml)
        );
    }

    private function extractTag(Response $response, string $tag): ?string
    {
        if (preg_match("#<{$tag}[^>]*>(.*?)</{$tag}>#s", $response->body(), $m)) {
            return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }
        return null;
    }

    private function resultIsSuccess(?string $result): bool
    {
        if ($result === null) return false;
        return (bool) preg_match('/success|^1$|^true$/i', $result);
    }

    /* =========================================================
     |  PAGE
     ========================================================= */

    public function residents(Request $request)
    {
        $user = auth()->user();

        $hostelQuery = Hostel::query()->orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }
        $hostels = $hostelQuery->get();

        $selectedHostel = $request->query('hostel_id');

        $residentQuery = Resident::with(['hostel', 'room', 'bed'])
            ->orderBy('name');

        if (!$user->isAdmin()) {
            $residentQuery->whereIn('hostel_id', $user->hostel_ids ?? []);
        }

        if ($selectedHostel) {
            $residentQuery->where('hostel_id', $selectedHostel);
        }

        $residents = $residentQuery->get();

        return view('admin.essl.resident', compact('residents', 'hostels', 'selectedHostel'));
    }

    /* =========================================================
     |  SYNC: ONE RESIDENT → HIS HOSTEL'S DEVICE
     ========================================================= */

    public function syncResident(Request $request)
    {
        $data = $request->validate([
            'resident_id' => 'required|exists:residents,id',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);

        if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this hostel.',
            ], 403);
        }

        if (empty($resident->employee_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Resident has no employee code.',
            ], 422);
        }

        $serial = $resident->hostel->biometric_device_id ?? null;

        if (empty($serial)) {
            return response()->json([
                'success' => false,
                'message' => "Hostel \"{$resident->hostel->hostel_name}\" has no eSSL device configured.",
            ], 422);
        }

        $result = $this->pushEmployeeToDevice(
            $resident->employee_code,
            $resident->name,
            $resident->card_number ?? '',
            $serial
        );

        if ($result['success']) {
            $resident->update(['last_sync_at' => now()]);
        }

        return response()->json([
            'success'    => $result['success'],
            'message'    => $result['success']
                ? "{$resident->name} ({$resident->employee_code}) added to device {$serial} ({$resident->hostel->hostel_name})."
                : 'eSSL replied: ' . ($result['error'] ?: 'empty'),
            'serial'     => $serial,
            'command_id' => $result['command_id'] ?? null,
            'synced_at'  => $result['success']
                ? $resident->last_sync_at->format('d M Y, h:i A')
                : null,
        ], $result['success'] ? 200 : 422);
    }

    /* =========================================================
     |  SYNC ALL RESIDENTS OF ONE HOSTEL
     ========================================================= */

    public function syncHostel(Request $request)
    {
        $data = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
        ]);

        if (!auth()->user()->hasAccessToHostel($data['hostel_id'])) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $hostel = Hostel::findOrFail($data['hostel_id']);

        if (empty($hostel->biometric_device_id)) {
            return response()->json([
                'success' => false,
                'message' => "Hostel \"{$hostel->hostel_name}\" has no eSSL device configured.",
            ], 422);
        }

        $residents = Resident::where('hostel_id', $hostel->id)
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code')
            ->get();

        $ok = 0; $fail = 0; $errors = [];

        foreach ($residents as $resident) {
            $r = $this->pushEmployeeToDevice(
                $resident->employee_code,
                $resident->name,
                $resident->card_number ?? '',
                $hostel->biometric_device_id
            );

            if ($r['success']) {
                $resident->update(['last_sync_at' => now()]);
                $ok++;
            } else {
                $fail++;
                $errors[] = "{$resident->employee_code}: {$r['error']}";
            }
        }

        return response()->json([
            'success' => $fail === 0,
            'message' => "Synced {$ok} residents to {$hostel->hostel_name}, {$fail} failed.",
            'synced'  => $ok,
            'failed'  => $fail,
            'errors'  => $errors,
        ]);
    }

    /* =========================================================
     |  BLOCK / UNBLOCK
     ========================================================= */

    public function blockUser(Request $request)
    {
        $data = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'block'       => 'required|boolean',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);

        if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this resident.',
            ], 403);
        }

        if (empty($resident->employee_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Resident has no employee code.',
            ], 422);
        }

        $serial = $resident->hostel->biometric_device_id ?? null;

        if (empty($serial)) {
            return response()->json([
                'success' => false,
                'message' => "Hostel \"{$resident->hostel->hostel_name}\" has no eSSL device configured.",
            ], 422);
        }

        $isBlock = (bool) $data['block'];

        try {
            $response = $this->soapCall('BlockUnblockUser', [
                'APIKey'       => $this->auth['api_key'],
                'EmployeeCode' => $resident->employee_code,
                'EmployeeName' => $resident->name,
                'SerialNumber' => $serial,
                'IsBlock'      => $isBlock ? 'true' : 'false',
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
                'CommandId'    => 0,
            ]);

            Log::info('eSSL BlockUnblockUser', [
                'resident_id'   => $resident->id,
                'employee_code' => $resident->employee_code,
                'serial'        => $serial,
                'is_block'      => $isBlock,
                'http_status'   => $response->status(),
                'body'          => $response->body(),
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device error: HTTP ' . $response->status(),
                    'body'    => $response->body(),
                ], 500);
            }

            $result    = $this->extractTag($response, 'BlockUnblockUserResult');
            $commandId = $this->extractTag($response, 'CommandId');

            if (!$this->resultIsSuccess($result)) {
                return response()->json([
                    'success' => false,
                    'message' => 'eSSL replied: ' . ($result ?: 'empty response'),
                ], 422);
            }

            $resident->update(['biometric_access' => !$isBlock]);

            $action = $isBlock ? 'blocked' : 'unblocked';

            return response()->json([
                'success'          => true,
                'message'          => "{$resident->name} ({$resident->employee_code}) {$action} on device {$serial}.",
                'command_id'       => $commandId,
                'blocked'          => $isBlock,
                'biometric_access' => (bool) $resident->biometric_access,
            ]);
        } catch (ConnectionException $e) {
            Log::error('eSSL BlockUnblockUser connection failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
                'url'         => $this->baseUrl,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device unreachable at ' . $this->baseUrl
                    . '. Check IP/port, network, and device power.',
                'detail'  => $e->getMessage(),
            ], 504);
        } catch (\Throwable $e) {
            Log::error('eSSL BlockUnblockUser failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  REUSABLE PUSH
     ========================================================= */

    private function pushEmployeeToDevice(
        string $employeeCode,
        string $employeeName,
        string $cardNumber,
        string $serial
    ): array {
        try {
            $response = $this->soapCall('AddEmployee', [
                'APIKey'       => $this->auth['api_key'],
                'EmployeeCode' => $employeeCode,
                'EmployeeName' => $employeeName,
                'CardNumber'   => $cardNumber,
                'SerialNumber' => $serial,
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
                'CommandId'    => 0,
            ]);

            Log::info('eSSL AddEmployee', [
                'serial'        => $serial,
                'employee_code' => $employeeCode,
                'http_status'   => $response->status(),
                'body'          => $response->body(),
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error'   => 'HTTP ' . $response->status(),
                    'body'    => $response->body(),
                ];
            }

            $result    = $this->extractTag($response, 'AddEmployeeResult');
            $commandId = $this->extractTag($response, 'CommandId');

            $ok = $this->resultIsSuccess($result);

            return [
                'success'    => $ok,
                'error'      => $ok ? null : ($result ?: 'empty'),
                'command_id' => $commandId,
            ];
        } catch (ConnectionException $e) {
            Log::error('eSSL AddEmployee connection failed', [
                'serial' => $serial,
                'url'    => $this->baseUrl,
                'error'  => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'Device unreachable at ' . $this->baseUrl
                    . ' — check IP/port/network. (' . $e->getMessage() . ')',
            ];
        } catch (\Throwable $e) {
            Log::error('eSSL AddEmployee failed', [
                'serial' => $serial,
                'error'  => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    /* =========================================================
     |  FINGERPRINT / FACE ENROLL
     ========================================================= */

    public function enrollFingerprint(Request $request)
    {
        $data = $request->validate([
            'resident_id'  => 'required|exists:residents,id',
            'finger_index' => 'nullable|integer|min:0|max:9',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);
        $serial   = $resident->hostel->biometric_device_id ?? null;

        if (empty($serial)) {
            return response()->json(['success' => false, 'message' => 'Hostel has no device.'], 422);
        }

        try {
            $response = $this->soapCall('EnrollUserFP', [
                'APIKey'            => $this->auth['api_key'],
                'EmployeeCode'      => $resident->employee_code,
                'FingerIndexNumber' => $data['finger_index'] ?? 1,
                'isOverWrite'       => 'true',
                'SerialNumber'      => $serial,
                'UserName'          => $this->auth['username'],
                'UserPassword'      => $this->auth['password'],
                'CommandId'         => 0,
            ]);

            $result = $this->extractTag($response, 'EnrollUserFPResult');

            return response()->json([
                'success' => $this->resultIsSuccess($result),
                'result'  => $result,
            ]);
        } catch (ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device unreachable at ' . $this->baseUrl,
                'detail'  => $e->getMessage(),
            ], 504);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function enrollFace(Request $request)
    {
        $data = $request->validate([
            'resident_id' => 'required|exists:residents,id',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);
        $serial   = $resident->hostel->biometric_device_id ?? null;

        if (empty($serial)) {
            return response()->json(['success' => false, 'message' => 'Hostel has no device.'], 422);
        }

        try {
            $response = $this->soapCall('EnrollUserFace', [
                'APIKey'       => $this->auth['api_key'],
                'EmployeeCode' => $resident->employee_code,
                'isOverWrite'  => 'true',
                'SerialNumber' => $serial,
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
                'CommandId'    => 0,
            ]);

            $result = $this->extractTag($response, 'EnrollUserFaceResult');

            return response()->json([
                'success' => $this->resultIsSuccess($result),
                'result'  => $result,
            ]);
        } catch (ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Device unreachable at ' . $this->baseUrl,
                'detail'  => $e->getMessage(),
            ], 504);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     |  UTILITIES / DIAGNOSTICS
     ========================================================= */

    public function test(Request $request)
    {
        return $this->commandStatus($request);
    }

    /**
     * Diagnostic endpoint: reports the configured URL, reachability,
     * and the raw SOAP response from the device.
     */
    public function diagnose(Request $request)
    {
        $url  = $this->baseUrl;
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?: 80;

        $tcpOpen = false;
        $tcpErr  = null;

        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($fp) {
            $tcpOpen = true;
            fclose($fp);
        } else {
            $tcpErr = "{$errno}: {$errstr}";
        }

        return response()->json([
            'configured_url'   => $url,
            'host'             => $host,
            'port'             => $port,
            'tcp_open'         => $tcpOpen,
            'tcp_error'        => $tcpErr,
            'username'         => $this->auth['username'],
            'api_key_set'      => $this->auth['api_key'] !== '',
            'mock'             => (bool) config('services.essl.mock'),
            'connect_timeout'  => $this->connectTimeout,
            'request_timeout'  => $this->timeout,
            'hint'             => $tcpOpen
                ? 'TCP reachable. If SOAP still fails, verify SOAPAction / path / credentials.'
                : 'TCP NOT reachable. Fix IP/port/network/firewall, or check that the device is powered on.',
        ]);
    }

    public function transactions(Request $request)
    {
        $serial = $request->query('serial');

        if (!$serial) {
            $hostel = Hostel::whereNotNull('biometric_device_id')->first();
            $serial = $hostel->biometric_device_id ?? null;
        }

        if (!$serial) {
            return response()->json(['success' => false, 'error' => 'No device serial.'], 422);
        }

        try {
            $response = $this->soapCall('GetTransactionsLog', [
                'FromDate'     => $request->query('from', ''),
                'ToDate'       => $request->query('to', ''),
                'SerialNumber' => $serial,
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
                'strDataList'  => 'Blank',
            ], 30);

            return response()->json([
                'success' => $response->successful(),
                'result'  => $this->extractTag($response, 'GetTransactionsLogResult'),
                'data'    => $this->extractTag($response, 'strDataList'),
            ], $response->successful() ? 200 : 500);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function commandStatus(Request $request)
    {
        try {
            $response = $this->soapCall('GetCommandStatus', [
                'CommandId'    => $request->query('command_id', ''),
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
            ], 15);

            $result = $this->extractTag($response, 'GetCommandStatusResult');

            return response()->json([
                'success' => $response->successful() && $this->resultIsSuccess($result),
                'result'  => $result,
            ], $response->successful() ? 200 : 500);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function addEmployee(Request $request)
    {
        $data = $request->validate([
            'hostel_id'     => 'required|exists:hostels,id',
            'employee_code' => 'required|string',
            'employee_name' => 'required|string',
            'card_number'   => 'nullable|string',
        ]);

        $hostel = Hostel::findOrFail($data['hostel_id']);
        $serial = $hostel->biometric_device_id;

        if (empty($serial)) {
            return response()->json([
                'success' => false,
                'message' => "Hostel \"{$hostel->hostel_name}\" has no device configured.",
            ], 422);
        }

        $result = $this->pushEmployeeToDevice(
            $data['employee_code'],
            $data['employee_name'],
            $data['card_number'] ?? '',
            $serial
        );

        return response()->json([
            'success'    => $result['success'],
            'message'    => $result['success'] ? 'OK' : ($result['error'] ?: 'failed'),
            'command_id' => $result['command_id'] ?? null,
        ], $result['success'] ? 200 : 422);
    }
}
