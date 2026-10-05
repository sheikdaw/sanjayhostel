<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Hostel;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EsslController extends Controller
{
    private const NS = 'http://tempuri.org/';

    private string $baseUrl;
    private array $auth;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.essl.url'), '?');

        $this->auth = [
            'api_key'  => (string) config('services.essl.api_key'),
            'username' => (string) config('services.essl.username'),
            'password' => (string) config('services.essl.password'),
        ];
    }

    /* =========================================================
     |  CORE: SOAP CALL
     ========================================================= */

    private function soapCall(string $method, array $params = [], int $timeout = 30): Response
    {
        if (config('services.essl.mock')) {
            return $this->mockResponse($method);
        }

        $inner = '';
        foreach ($params as $name => $value) {
            $safe = is_int($value)
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
            'url' => $this->baseUrl,
            'xml' => $xml,
        ]);

        return Http::timeout($timeout)
            ->withHeaders(['SOAPAction' => '"' . self::NS . $method . '"'])
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
            'IsBlock'      => $isBlock ? 'true' : 'false',  // ✅ lowercase
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
    } catch (\Throwable $e) {
        Log::error('eSSL BlockUnblockUser failed', [
            'resident_id' => $resident->id,
            'error'       => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Device unreachable: ' . $e->getMessage(),
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

            return [
                'success'    => $this->resultIsSuccess($result),
                'error'      => $this->resultIsSuccess($result) ? null : ($result ?: 'empty'),
                'command_id' => $commandId,
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
            'isOverWrite'       => 'true',  // ✅ lowercase
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
            'isOverWrite'  => 'true',  // ✅ lowercase
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
    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
    /* =========================================================
     |  UTILITIES
     ========================================================= */

    public function test(Request $request)
    {
        return $this->commandStatus($request);
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
