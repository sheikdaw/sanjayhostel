<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Services\EsslService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EsslTestController extends Controller
{
    public function __construct(private EsslService $essl) {}

    /* ── Page ── */
    public function index()
    {
        $residents = Resident::with('hostel')
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code')
            ->orderBy('name')
            ->get();

        return view('essl-test', compact('residents'));
    }

    /* ── Add ONE resident — full raw response ── */
    public function addOne(int $id)
    {
        $resident = Resident::with('hostel')->findOrFail($id);

        if (!$resident->hostel?->biometric_device_id) {
            return response()->json([
                'success' => false,
                'step'    => 'validation',
                'message' => 'No device assigned to hostel.',
            ], 422);
        }

        $serial = $resident->hostel->biometric_device_id;
        $code   = (string) $resident->employee_code;

        // STEP 1: Web DB
        $db = $this->essl->addEmployeeToDb([
            'code'      => $code,
            'name'      => $resident->name,
            'gender'    => $resident->gender ?? null,
            'join_date' => optional($resident->joining_date)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'status'    => 'Working',
        ]);

        // STEP 2: Device
        $device = $this->essl->addEmployee(
            $code,
            $resident->name,
            $serial,
            (string) ($resident->card_number ?? '')
        );

        // STEP 3: Command status (already called inside addEmployee)
        $cmdSt = $device['command_status'] ?? null;

        return response()->json([
            'resident' => [
                'id'            => $resident->id,
                'name'          => $resident->name,
                'employee_code' => $code,
                'serial'        => $serial,
            ],
            'web_db' => [
                'success' => $db['success'],
                'message' => $db['message'],
                'raw'     => $db['raw'],
            ],
            'device' => [
                'success'           => $device['success'],
                'message'           => $device['message'],
                'sent_command_id'   => $device['sent_command_id']   ?? null,
                'server_command_id' => $device['server_command_id'] ?? $device['command_id'] ?? null,
                'raw'               => $device['raw'],
            ],
            'command_status' => $cmdSt,
            'final_verdict'  => $this->verdict($db, $device, $cmdSt),
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /* ── Add ALL ACTIVE residents ── */
    public function addAll(Request $request)
    {
        $residents = Resident::with('hostel')
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code')
            ->get();

        $results = [];

        foreach ($residents as $r) {
            $serial = $r->hostel?->biometric_device_id;

            if (!$serial) {
                $results[] = [
                    'code'    => $r->employee_code,
                    'name'    => $r->name,
                    'verdict' => '❌ No device',
                ];
                continue;
            }

            $device = $this->essl->addEmployee(
                (string) $r->employee_code,
                $r->name,
                $serial,
                (string) ($r->card_number ?? '')
            );

            $cmdSt = $device['command_status']['message'] ?? null;

            $results[] = [
                'code'              => $r->employee_code,
                'name'              => $r->name,
                'serial'            => $serial,
                'success'           => $device['success'],
                'message'           => $device['message'],
                'sent_command_id'   => $device['sent_command_id']   ?? null,
                'server_command_id' => $device['server_command_id'] ?? $device['command_id'] ?? null,
                'command_status'    => $cmdSt,
            ];
        }

        return response()->json([
            'total'   => count($results),
            'success' => collect($results)->where('success', true)->count(),
            'failed'  => collect($results)->where('success', false)->count(),
            'results' => $results,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /* ── Command status ── */
    public function command(string $id)
    {
        return response()->json(
            $this->essl->getCommandStatus($id),
            200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    private function verdict($db, $device, $cmdSt): string
    {
        if (empty($db['success']))     return '❌ Web DB failed';
        if (empty($device['success'])) return '❌ Device call failed';

        $msg = strtolower((string) ($cmdSt['message'] ?? ''));

        if (str_contains($msg, 'success'))            return '✅ Added on device';
        if (str_contains($msg, 'pending'))            return '⏳ Device still processing';
        if (str_contains($msg, 'not exist'))          return '⚠️ Command ID not found — check server';
        if (str_contains($msg, 'fail'))               return '❌ Device rejected';

        return '⚠️ Queued — check device';
    }
}
