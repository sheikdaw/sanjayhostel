<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Resident;
use App\Services\EsslService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EsslController extends Controller
{
    public function __construct(private EsslService $essl) {}

    /* ───────────── Page ───────────── */

    public function residents(Request $request)
    {
        $hostels = Hostel::where('status', 'active')
            ->orderBy('hostel_name')
            ->get();

        return view('admin.essl.residents', compact('hostels'));
    }

    public function getResidents(Request $request): JsonResponse
    {
        $hostelId = $request->query('hostel_id', 'all');

        $residents = Resident::with([
                'hostel:id,hostel_name,biometric_device_id',
                'room:id,room_no',
                'bed:id,bed_no',
            ])
            ->when($hostelId !== 'all', fn($q) => $q->where('hostel_id', $hostelId))
            ->orderBy('name')
            ->get([
                'id', 'name', 'employee_code', 'hostel_id', 'room_id', 'bed_id',
                'status', 'biometric_access', 'last_sync_at',
            ]);

        return response()->json([
            'success'   => true,
            'residents' => $residents,
            'total'     => $residents->count(),
        ]);
    }

    /* ───────────── Sync ───────────── */

    public function syncResident(Request $request): JsonResponse
    {
        $data = $request->validate([
            'resident_id' => 'required|integer|exists:residents,id',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);
        $r        = $this->doSync($resident);

        return response()->json($this->singleResponse($resident, $r));
    }

    public function bulkSync(Request $request): JsonResponse
    {
        set_time_limit(300);

        $data = $request->validate([
            'resident_ids'   => 'required|array|min:1|max:100',
            'resident_ids.*' => 'integer|exists:residents,id',
        ]);

        $residents = Resident::with('hostel')
            ->whereIn('id', $data['resident_ids'])
            ->get();

        return $this->summarise($residents, fn($r) => $this->doSync($r), 'synced');
    }

    public function syncHostel(Request $request): JsonResponse
    {
        set_time_limit(300);

        $data = $request->validate([
            'hostel_id' => 'required|integer|exists:hostels,id',
        ]);

        $residents = Resident::with('hostel')
            ->where('hostel_id', $data['hostel_id'])
            ->where('status', 'ACTIVE')
            ->get();

        if ($residents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No ACTIVE residents in this hostel.',
                'synced'  => 0,
                'failed'  => 0,
                'results' => [],
            ]);
        }

        return $this->summarise($residents, fn($r) => $this->doSync($r), 'synced');
    }

    /* ───────────── Block / Unblock ───────────── */

    public function blockUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'resident_id' => 'required|integer|exists:residents,id',
            'block'       => 'required|boolean',
        ]);

        $resident = Resident::with('hostel')->findOrFail($data['resident_id']);
        $block    = (bool) $data['block'];

        $r = $this->doBlock($resident, $block);

        return response()->json($this->singleResponse($resident, $r));
    }

    public function bulkBlock(Request $request): JsonResponse
    {
        set_time_limit(300);

        $data = $request->validate([
            'resident_ids'   => 'required|array|min:1|max:100',
            'resident_ids.*' => 'integer|exists:residents,id',
            'block'          => 'required|boolean',
        ]);

        $block     = (bool) $data['block'];
        $residents = Resident::with('hostel')
            ->whereIn('id', $data['resident_ids'])
            ->get();

        return $this->summarise(
            $residents,
            fn($r) => $this->doBlock($r, $block),
            $block ? 'blocked' : 'unblocked'
        );
    }

    /* ───────────── Command status ───────────── */

    public function commandStatus(Request $request): JsonResponse
    {
        $data = $request->validate(['command_id' => 'required|string']);

        return response()->json($this->essl->getCommandStatus($data['command_id']));
    }

    /* ───────────── Core operations ───────────── */

    private function validateForDevice(Resident $resident): ?string
    {
        if (!$resident->employee_code) {
            return 'No employee code.';
        }
        if (!$resident->hostel || !$resident->hostel->biometric_device_id) {
            return 'Hostel has no biometric device.';
        }
        return null;
    }

    /**
     * STEP 1: AddMultipleEmployeesToDB (first sync only)
     * STEP 2: AddEmployee → device queue
     * STEP 3 (FIX #1): If DB says biometric_access = false,
     *                  immediately block on device after add.
     *
     * @param bool $keepBlocked  When true, re-apply block if resident is
     *                           currently marked blocked in DB.
     */
    private function doSync(Resident $resident, bool $keepBlocked = true): array
    {
        if ($resident->status !== 'ACTIVE') {
            return ['success' => false, 'message' => "{$resident->name}: not ACTIVE, skipped."];
        }
        if ($err = $this->validateForDevice($resident)) {
            return ['success' => false, 'message' => "{$resident->name}: {$err}"];
        }

        $firstSync = empty($resident->last_sync_at);
        $wasBlocked = !$resident->biometric_access;

        // STEP 1
        if ($firstSync) {
            $db = $this->essl->addEmployeeToDb([
                'code'        => $resident->employee_code,
                'name'        => $resident->name,
                'gender'      => $resident->gender ?? null,
                'join_date'   => optional($resident->joining_date)->format('Y-m-d')
                                 ?? now()->format('Y-m-d'),
                'status'      => 'Working',
                'resign_date' => '',
            ]);

            if (!$db['success']) {
                Log::warning('eSSL DB add failed', [
                    'resident_id' => $resident->id,
                    'code'        => $resident->employee_code,
                    'result'      => $db,
                ]);
                return [
                    'success' => false,
                    'message' => "{$resident->name}: DB add failed - {$db['message']}",
                ];
            }
        }

        // STEP 2 — device add
        $res = $this->essl->addEmployee(
            (string) $resident->employee_code,
            $resident->name,
            $resident->hostel->biometric_device_id,
            (string) ($resident->card_number ?? '')
        );

        if (empty($res['success'])) {
            $msg = ($firstSync ? 'Added to web DB' : 'Re-synced')
                 . ', device FAILED: ' . ($res['message'] ?? 'unknown');
            $cmdId = $res['server_command_id'] ?? $res['sent_command_id'] ?? null;
            if ($cmdId) $msg .= " (Cmd#{$cmdId})";

            return ['success' => false, 'message' => "{$resident->name}: {$msg}"];
        }

        $resident->last_sync_at = now();
        $resident->save();

        // STEP 3 (FIX #1) — re-apply block on device if DB says blocked
        $blockMsg = '';
        if ($keepBlocked && $wasBlocked) {
            $b = $this->essl->blockUnblock(
                (string) $resident->employee_code,
                $resident->name,
                $resident->hostel->biometric_device_id,
                true
            );
            $blockMsg = !empty($b['success'])
                ? ' + re-blocked on device'
                : ' (re-block failed: ' . ($b['message'] ?? 'unknown') . ')';
        }

        $msg = ($firstSync ? 'Added to web DB' : 'Re-synced') . ' + queued to device' . $blockMsg;
        $cmdId = $res['server_command_id'] ?? $res['sent_command_id'] ?? null;
        if ($cmdId) $msg .= " (Cmd#{$cmdId})";

        return ['success' => true, 'message' => "{$resident->name}: {$msg}"];
    }

    /**
     * Block / Unblock on the device.
     * Non-ACTIVE resident → permanent block (unblock rejected).
     */
    private function doBlock(Resident $resident, bool $block): array
    {
        if ($resident->status !== 'ACTIVE' && !$block) {
            return [
                'success' => false,
                'message' => "{$resident->name}: {$resident->status} resident, cannot be unblocked.",
            ];
        }

        if ($err = $this->validateForDevice($resident)) {
            return ['success' => false, 'message' => "{$resident->name}: {$err}"];
        }

        $serial = $resident->hostel->biometric_device_id;
        $code   = (string) $resident->employee_code;

        // Not yet synced
        if (!$resident->last_sync_at) {
            if ($block) {
                $resident->update([
                    'biometric_access'   => false,
                    'access_disabled_at' => now(),
                ]);
                return [
                    'success' => true,
                    'message' => "{$resident->name}: marked blocked (not on device yet).",
                ];
            }

            // Unblock → must first push (FIX #2: keepBlocked=false so no extra block)
            $add = $this->doSync($resident, false);
            if (!$add['success']) return $add;

            $resident->refresh();
        }

        $res = $this->essl->blockUnblock($code, $resident->name, $serial, $block);

        // Fallback: unblock failed → employee not on device → sync, retry
        if (!$block
            && empty($res['success'])
            && stripos($res['message'] ?? '', 'not found') !== false) {

            $add = $this->doSync($resident, false);   // FIX #2
            if ($add['success']) {
                $resident->refresh();
                $res = $this->essl->blockUnblock($code, $resident->name, $serial, false);
            }
        }

        if (!empty($res['success'])) {
            $resident->biometric_access = !$block;
            $resident->last_sync_at     = now();

            if ($block) {
                $resident->access_disabled_at = now();
            } else {
                $resident->access_enabled_at = now();
            }

            $resident->save();
        }

        $msg = $res['message'] ?? 'unknown';
        $cmdId = $res['server_command_id'] ?? $res['command_id'] ?? null;
        if ($cmdId) $msg .= " (Cmd#{$cmdId})";

        return ['success' => !empty($res['success']), 'message' => "{$resident->name}: {$msg}"];
    }

    /* ───────────── Helpers ───────────── */

    private function stateOf(Resident $resident): array
    {
        return [
            'id'               => $resident->id,
            'biometric_access' => (bool) $resident->biometric_access,
            'unblocked'        => $resident->status === 'ACTIVE'
                                  && (bool) $resident->biometric_access,
            'last_sync_at'     => optional($resident->last_sync_at)->toDateTimeString(),
        ];
    }

    private function singleResponse(Resident $resident, array $r): array
    {
        $resident->refresh();

        return array_merge(
            ['success' => $r['success'], 'message' => $r['message']],
            $this->stateOf($resident)
        );
    }

    private function summarise($residents, callable $op, string $verb): JsonResponse
    {
        $ok      = 0;
        $failed  = [];
        $results = [];

        foreach ($residents as $resident) {
            $r = $op($resident);

            if ($r['success']) {
                $ok++;
            } else {
                $failed[] = $r['message'];
            }

            $results[] = array_merge(
                ['success' => $r['success'], 'message' => $r['message']],
                $this->stateOf($resident)
            );
        }

        $total   = $residents->count();
        $message = "{$ok}/{$total} {$verb}.";

        if ($failed) {
            $message .= ' Failed: ' . implode(' | ', array_slice($failed, 0, 3))
                      . (count($failed) > 3 ? ' …' : '');
        }

        return response()->json([
            'success' => $ok > 0 && empty($failed),
            'message' => $message,
            'synced'  => $ok,
            'failed'  => count($failed),
            'results' => $results,
        ]);
    }
}
