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

    /* ═══════════════════════════════════════════════════════════
     |  🔑 PUBLIC API — used by PaymentController
     ═══════════════════════════════════════════════════════════ */

    public function syncResidentAccess(Resident $resident, bool $block): array
    {
        if (!$resident->relationLoaded('hostel')) {
            $resident->load('hostel');
        }

        try {
            return $this->doBlock($resident, $block);
        } catch (\Throwable $e) {
            Log::error('syncResidentAccess failed', [
                'resident_id' => $resident->id,
                'block'       => $block,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Access sync error: ' . $e->getMessage(),
            ];
        }
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
     * Sync a resident to the device.
     *
     * Fire-and-forget. No GetCommandStatus polling.
     *
     * Steps:
     *   1. AddMultipleEmployeesToDB (first sync only — synchronous web DB call)
     *   2. AddEmployee            → queue to device
     *   3. BlockUnblockUser(true) → queue to device (if DB says blocked)
     *
     * Commands queue in order (CommandId is monotonic), so the device always
     * processes AddEmployee before BlockUnblockUser. No race, no poll.
     */
    private function doSync(Resident $resident, bool $keepBlocked = true): array
    {
        if ($resident->status !== 'ACTIVE') {
            return ['success' => false, 'message' => "{$resident->name}: not ACTIVE, skipped."];
        }
        if ($err = $this->validateForDevice($resident)) {
            return ['success' => false, 'message' => "{$resident->name}: {$err}"];
        }

        $firstSync  = empty($resident->last_sync_at);
        $wasBlocked = !$resident->biometric_access;

        // STEP 1 — web DB (first time only)
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

        // STEP 2 — queue AddEmployee to device
        $res = $this->essl->addEmployee(
            (string) $resident->employee_code,
            $resident->name,
            $resident->hostel->biometric_device_id,
            (string) ($resident->card_number ?? '')
        );

        if (empty($res['success'])) {
            $msg   = ($firstSync ? 'Added to web DB' : 'Re-synced')
                   . ', device FAILED: ' . ($res['message'] ?? 'unknown');
            $cmdId = $res['server_command_id'] ?? $res['sent_command_id'] ?? null;
            if ($cmdId) $msg .= " (Cmd#{$cmdId})";

            return ['success' => false, 'message' => "{$resident->name}: {$msg}"];
        }

        $addCmdId = $res['server_command_id'] ?? $res['sent_command_id'] ?? null;

        // STEP 3 — queue BlockUnblockUser(true) right after (no poll)
        $blockMsg = '';
        if ($keepBlocked && $wasBlocked) {
            $b = $this->essl->blockUnblock(
                (string) $resident->employee_code,
                $resident->name,
                $resident->hostel->biometric_device_id,
                true
            );

            if (!empty($b['success'])) {
                $bCmdId   = $b['server_command_id'] ?? $b['sent_command_id'] ?? null;
                $blockMsg = ' + block queued' . ($bCmdId ? " (Cmd#{$bCmdId})" : '');
            } else {
                $blockMsg = ' (block queue FAILED: ' . ($b['message'] ?? 'unknown') . ')';
            }
        }

        // Mark synced immediately — device will catch up on its next poll
        $resident->last_sync_at = now();
        $resident->save();

        $msg = ($firstSync ? 'Added to web DB' : 'Re-synced')
             . ($addCmdId ? " (Cmd#{$addCmdId})" : '')
             . $blockMsg;

        return ['success' => true, 'message' => "{$resident->name}: {$msg}"];
    }

    /**
     * Block / Unblock on the device.
     * Fire-and-forget. No polling — device applies on next poll cycle.
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

            $add = $this->doSync($resident, false);
            if (!$add['success']) return $add;

            $resident->refresh();
        }

        $res = $this->essl->blockUnblock($code, $resident->name, $serial, $block);

        // Unblock fallback: user not on device → push then retry
        if (!$block
            && empty($res['success'])
            && stripos($res['message'] ?? '', 'not found') !== false) {

            $add = $this->doSync($resident, false);
            if ($add['success']) {
                $resident->refresh();
                $res = $this->essl->blockUnblock($code, $resident->name, $serial, false);
            }
        }

        if (empty($res['success'])) {
            $msg   = $res['message'] ?? 'unknown';
            $cmdId = $res['server_command_id'] ?? $res['command_id'] ?? null;
            if ($cmdId) $msg .= " (Cmd#{$cmdId})";
            return ['success' => false, 'message' => "{$resident->name}: {$msg}"];
        }

        // Queue accepted → update DB immediately
        $resident->biometric_access = !$block;
        $resident->last_sync_at     = now();

        if ($block) {
            $resident->access_disabled_at = now();
        } else {
            $resident->access_enabled_at = now();
        }

        $resident->save();

        $verb  = $block ? 'blocked (queued)' : 'unblocked (queued)';
        $msg   = "{$resident->name}: {$verb}";
        $cmdId = $res['server_command_id'] ?? $res['command_id'] ?? null;
        if ($cmdId) $msg .= " (Cmd#{$cmdId})";

        return ['success' => true, 'message' => $msg];
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
