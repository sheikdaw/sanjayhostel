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

        return Http::connectTimeout($this->connectTimeout)
            ->timeout($timeout ?? $this->timeout)
            ->withHeaders([
                'SOAPAction'   => '"' . self::NS . $method . '"',
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
        $body = trim($response->body());
        if ($body === '' || strpos($body, '<') === false) return null;
        if (stripos($body, '<html') !== false || stripos($body, '<!doctype html') !== false) return null;

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadXML($body);
        libxml_clear_errors();

        if (!$loaded) {
            if (preg_match("#<(?:\w+:)?{$tag}\b[^>]*>(.*?)</(?:\w+:)?{$tag}>#s", $body, $m)) {
                return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_XML1, 'UTF-8'));
            }
            return null;
        }

        $nodes = $dom->getElementsByTagNameNS('*', $tag);
        if ($nodes->length > 0) {
            return trim(html_entity_decode($nodes->item(0)->textContent, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }
        $nodes = $dom->getElementsByTagName($tag);
        if ($nodes->length > 0) {
            return trim(html_entity_decode($nodes->item(0)->textContent, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }

        return null;
    }

    private function resultIsSuccess(?string $result): bool
    {
        if ($result === null) return false;
        $r = strtolower(trim($result));
        return $r === 'success' || $r === '1' || $r === 'true' || strpos($r, 'success') !== false;
    }


    public function syncResidentAccess(Resident $resident, bool $block): array
    {
        $resident->loadMissing('hostel');

        if (empty($resident->employee_code)) {
            return ['success' => false, 'message' => 'No employee code', 'blocked' => $block];
        }

        $serial = $resident->hostel->biometric_device_id ?? null;

        if (empty($serial)) {
            return ['success' => false, 'message' => 'No device', 'blocked' => $block];
        }

        try {
            if ($block) {
                return $this->applyBlock($resident);
            }
            return $this->applyUnblock($resident, $serial);
        } catch (ConnectionException $e) {
            Log::error('syncResidentAccess — unreachable', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Device unreachable', 'blocked' => $block];
        } catch (\Throwable $e) {
            Log::error('syncResidentAccess — exception', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => $e->getMessage(), 'blocked' => $block];
        }
    }

    /**
     * BLOCK — DB only. NEVER touches device. NEVER deletes templates.
     */
    private function applyBlock(Resident $resident): array
    {
        $resident->update([
            'biometric_access'   => false,
            'access_disabled_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => "{$resident->name} blocked in system. Templates preserved.",
            'method'  => 'DB_ONLY',
            'blocked' => true,
        ];
    }

    /**
     * UNBLOCK — push to device. Only allowed when status === 'ACTIVE'.
     */
    private function applyUnblock(Resident $resident, string $serial): array
    {
        if ($resident->status !== 'ACTIVE') {
            return $this->applyBlock($resident);
        }

        $add = $this->pushEmployeeToDevice(
            $resident->employee_code,
            $resident->name,
            $resident->card_number ?? '',
            $serial
        );

        if (!$add['success']) {
            return ['success' => false, 'message' => 'AddEmployee failed: ' . $add['error'], 'blocked' => false];
        }

        $response = $this->soapCall('SetUserDoorAccess', [
            'APIKey'       => $this->auth['api_key'],
            'EmployeeCode' => $resident->employee_code,
            'SerialNumber' => $serial,
            'DoorId'       => 1,
            'IsAllow'      => 'true',
            'UserName'     => $this->auth['username'],
            'UserPassword' => $this->auth['password'],
            'CommandId'    => (string) $resident->id,
        ]);

        $ok = $response->successful()
            && $this->resultIsSuccess($this->extractTag($response, 'SetUserDoorAccessResult'));

        $resident->update([
            'biometric_access'  => true,
            'access_enabled_at' => now(),
            'last_sync_at'      => now(),
        ]);

        return [
            'success' => true,
            'message' => "{$resident->name} unblocked on device {$serial}",
            'method'  => $ok ? 'SetUserDoorAccess' : 'AddEmployee',
            'blocked' => false,
        ];
    }

    private function pushEmployeeToDevice(string $code, string $name, string $card, string $serial): array
    {
        try {
            $response = $this->soapCall('AddEmployee', [
                'APIKey'       => $this->auth['api_key'],
                'EmployeeCode' => $code,
                'EmployeeName' => $name,
                'CardNumber'   => $card,
                'SerialNumber' => $serial,
                'UserName'     => $this->auth['username'],
                'UserPassword' => $this->auth['password'],
                'CommandId'    => 0,
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'HTTP ' . $response->status()];
            }

            $result = $this->extractTag($response, 'AddEmployeeResult');

            return [
                'success'    => $this->resultIsSuccess($result),
                'error'      => $this->resultIsSuccess($result) ? null : ($result ?: 'empty'),
                'command_id' => $this->extractTag($response, 'CommandId'),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
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

        $residentQuery = Resident::with(['hostel', 'room', 'bed'])->orderBy('name');

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
     |  SYNC — ONE RESIDENT
     ========================================================= */

    public function syncResident(Request $request)
    {
        $data = $request->validate(['resident_id' => 'required|exists:residents,id']);
        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);

        if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        if ($resident->status !== 'ACTIVE') {
            $this->applyBlock($resident);
            return response()->json([
                'success' => false,
                'message' => "{$resident->name} is not ACTIVE — blocked instead.",
            ], 422);
        }

        $result = $this->syncResidentAccess($resident, false);

        return response()->json([
            'success'   => $result['success'],
            'message'   => $result['message'],
            'synced_at' => optional($resident->fresh()->last_sync_at)->format('d M Y, h:i A'),
        ], $result['success'] ? 200 : 422);
    }

    /* =========================================================
     |  SYNC — WHOLE HOSTEL
     ========================================================= */

    public function syncHostel(Request $request)
    {
        $data = $request->validate(['hostel_id' => 'required|exists:hostels,id']);

        if (!auth()->user()->hasAccessToHostel($data['hostel_id'])) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $hostel = Hostel::findOrFail($data['hostel_id']);

        if (empty($hostel->biometric_device_id)) {
            return response()->json(['success' => false, 'message' => 'No device.'], 422);
        }

        $residents = Resident::where('hostel_id', $hostel->id)
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code')
            ->get();

        $ok = 0; $fail = 0; $errors = [];

        foreach ($residents as $resident) {
            $r = $this->syncResidentAccess($resident, false);
            if ($r['success']) $ok++;
            else { $fail++; $errors[] = "{$resident->employee_code}: {$r['message']}"; }
        }

        return response()->json([
            'success' => $fail === 0,
            'message' => "Synced {$ok}, failed {$fail}.",
            'synced'  => $ok,
            'failed'  => $fail,
            'errors'  => $errors,
        ]);
    }

    /* =========================================================
     |  BULK: SYNC SELECTED
     ========================================================= */

    public function bulkSync(Request $request)
    {
        $data = $request->validate([
            'resident_ids'   => 'required|array|min:1',
            'resident_ids.*' => 'integer|exists:residents,id',
        ]);

        $user = auth()->user();
        $residents = Resident::with('hostel')
            ->whereIn('id', $data['resident_ids'])
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code')
            ->get();

        $ok = 0; $fail = 0; $errors = [];

        foreach ($residents as $resident) {
            if (!$user->hasAccessToHostel($resident->hostel_id)) {
                $fail++; $errors[] = "{$resident->name}: no access"; continue;
            }

            $r = $this->syncResidentAccess($resident, false);

            if (!empty($r['success'])) $ok++;
            else { $fail++; $errors[] = "{$resident->employee_code}: " . ($r['message'] ?? 'failed'); }
        }

        return response()->json([
            'success' => $fail === 0,
            'message' => "Synced {$ok}, failed {$fail}.",
            'synced'  => $ok,
            'failed'  => $fail,
            'errors'  => $errors,
        ]);
    }

    /* =========================================================
     |  BLOCK / UNBLOCK (single)
     ========================================================= */

    public function blockUser(Request $request)
    {
        $data = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'block'       => 'required|boolean',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);

        if (!auth()->user()->hasAccessToHostel($resident->hostel_id)) {
            return response()->json(['success' => false, 'message' => 'No access.'], 403);
        }

        $isBlock = (bool) $data['block'];
        $result  = $this->syncResidentAccess($resident, $isBlock);

        return response()->json([
            'success'          => $result['success'],
            'message'          => $result['message'],
            'blocked'          => $isBlock,
            'biometric_access' => (bool) $resident->fresh()->biometric_access,
            'method'           => $result['method'] ?? null,
        ], $result['success'] ? 200 : 422);
    }

    /* =========================================================
     |  BULK: BLOCK / UNBLOCK SELECTED
     ========================================================= */

    public function bulkBlock(Request $request)
    {
        $data = $request->validate([
            'resident_ids'   => 'required|array|min:1',
            'resident_ids.*' => 'integer|exists:residents,id',
            'block'          => 'required|boolean',
        ]);

        $user    = auth()->user();
        $isBlock = (bool) $data['block'];

        $residents = Resident::with('hostel')
            ->whereIn('id', $data['resident_ids'])
            ->get();

        $ok = 0; $fail = 0; $errors = [];

        foreach ($residents as $resident) {
            if (!$user->hasAccessToHostel($resident->hostel_id)) {
                $fail++; $errors[] = "{$resident->name}: no access"; continue;
            }

            $result = $this->syncResidentAccess($resident, $isBlock);

            if (!empty($result['success'])) $ok++;
            else { $fail++; $errors[] = "{$resident->employee_code}: " . ($result['message'] ?? 'failed'); }
        }

        $action = $isBlock ? 'blocked' : 'unblocked';

        return response()->json([
            'success' => $fail === 0,
            'message' => "{$ok} resident(s) {$action}, {$fail} failed.",
            'blocked' => $ok,
            'failed'  => $fail,
            'errors'  => $errors,
        ]);
    }
}
