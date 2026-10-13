<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use App\Services\EsslService;
use Carbon\Carbon;
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
                'id',
                'name',
                'employee_code',
                'hostel_id',
                'room_id',
                'bed_id',
                'status',
                'biometric_access',
                'last_sync_at',
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

    /* ═══════════════════════════════════════════════════════════
     |  🔑 PUBLIC API — used by PaymentController
     |  Auto block / unblock after payment save
     ═══════════════════════════════════════════════════════════ */

    /**
     * Public wrapper for doBlock().
     *
     * PaymentController calls this after every payment save:
     *   - balance = 0  →  syncResidentAccess($resident, false)  → UNBLOCK
     *   - balance > 0  →  syncResidentAccess($resident, true)   → BLOCK
     */
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

    /* ═══════════════════════════════════════════════════════════
     |  🆕 PAYMENT + DATE BASED ACCESS DECISION
     ═══════════════════════════════════════════════════════════ */

    /**
     * Decide if a resident should be BLOCKED based on:
     *   - Current date (day of month)
     *   - Payment status (this month + last month)
     *
     * Rule:
     *   - day ≤ 10   → ALWAYS UNBLOCKED (grace period)
     *   - day > 10   → check current + previous month
     *                  if either has pending/unpaid balance → BLOCK
     *                  else → UNBLOCK
     *
     * @param  Resident $resident
     * @return array{should_block: bool, reason: string}
     */
    private function decideAccess(Resident $resident): array
    {
        $today = Carbon::now();
        $day   = (int) $today->day;

        // ── Grace period: 1st–10th → always allow ──
        if ($day <= 10) {
            return [
                'should_block' => false,
                'reason'       => "Grace period (day {$day}) — allowed",
            ];
        }

        // ── After 10th → check payments ──
        $currentMonth = (int) $today->month;
        $currentYear  = (int) $today->year;

        $prev = $today->copy()->subMonthNoOverflow();
        $prevMonth = (int) $prev->month;
        $prevYear  = (int) $prev->year;

        $currentStatus = $this->monthPaymentStatus($resident, $currentMonth, $currentYear);
        $prevStatus    = $this->monthPaymentStatus($resident, $prevMonth, $prevYear);

        // Don't count the previous month if the resident joined this month
        $joinDate = $resident->joining_date
            ? Carbon::parse($resident->joining_date)
            : null;

        $skipPrev = $joinDate
            && $joinDate->year === $currentYear
            && $joinDate->month === $currentMonth;

        $reasons = [];
        $block   = false;

        if (in_array($currentStatus, ['UNPAID', 'PARTIAL', 'PENDING'])) {
            $block     = true;
            $reasons[] = "current month: {$currentStatus}";
        }

        if (!$skipPrev && in_array($prevStatus, ['UNPAID', 'PARTIAL', 'PENDING'])) {
            $block     = true;
            $reasons[] = "previous month: {$prevStatus}";
        }

        return [
            'should_block' => $block,
            'reason'       => $block
                ? 'After 10th — ' . implode(', ', $reasons)
                : 'After 10th — all paid',
        ];
    }

    /**
     * Returns PAID | PARTIAL | UNPAID | PENDING
     *   PENDING = payment row exists but nothing paid yet (rare)
     */
    private function monthPaymentStatus(Resident $resident, int $month, int $year): string
    {
        $payment = Payment::where('resident_id', $resident->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payment) {
            return 'UNPAID';
        }

        $paid    = (float) $payment->cash_paid_amount + (float) $payment->upi_paid_amount;
        $balance = (float) $payment->balance_amount;

        if ($paid <= 0) {
            return 'PENDING';
        }
        if ($balance > 0) {
            return 'PARTIAL';
        }
        return 'PAID';
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
     * Sync to device, then apply block/unblock based on payment + date.
     *
     * No more `last_sync_at` gating. Every sync:
     *   1. Push employee to device (skip DB add if already synced)
     *   2. Evaluate payment + date
     *   3. Block or unblock accordingly
     */
    private function doSync(Resident $resident): array
    {
        if ($resident->status !== 'ACTIVE') {
            return ['success' => false, 'message' => "{$resident->name}: not ACTIVE, skipped."];
        }

        if ($err = $this->validateForDevice($resident)) {
            return ['success' => false, 'message' => "{$resident->name}: {$err}"];
        }

        $firstSync = empty($resident->last_sync_at);

        // ── STEP 1: First time → add to eSSL web DB ──
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

        // ── STEP 2: Push employee to device ──
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

        // ── STEP 3: Payment + date based block/unblock ──
        $decision   = $this->decideAccess($resident);
        $wantBlock  = $decision['should_block'];

        // Apply to device
        $b = $this->essl->blockUnblock(
            (string) $resident->employee_code,
            $resident->name,
            $resident->hostel->biometric_device_id,
            $wantBlock
        );

        $blockMsg = '';
        if (!empty($b['success'])) {
            $resident->biometric_access = !$wantBlock;
            if ($wantBlock) {
                $resident->access_disabled_at = now();
            } else {
                $resident->access_enabled_at = now();
            }
            $resident->save();

            $blockMsg = $wantBlock ? ' → BLOCKED' : ' → UNBLOCKED';
        } else {
            $blockMsg = ' (access sync failed: ' . ($b['message'] ?? 'unknown') . ')';
        }

        $cmdId = $res['server_command_id'] ?? $res['sent_command_id'] ?? null;
        $cmdTxt = $cmdId ? " (Cmd#{$cmdId})" : '';

        return [
            'success' => true,
            'message' => "{$resident->name}: "
                . ($firstSync ? 'Added to web DB' : 'Re-synced')
                . " + queued to device{$cmdTxt}{$blockMsg} — {$decision['reason']}",
        ];
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

        // Not yet synced → push first
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

            // Unblock → first push (no extra block since not synced)
            $add = $this->doSync($resident);
            if (!$add['success']) return $add;

            $resident->refresh();
        }

        $res = $this->essl->blockUnblock($code, $resident->name, $serial, $block);

        // Fallback: unblock failed → employee not on device → sync, retry
        if (
            !$block
            && empty($res['success'])
            && stripos($res['message'] ?? '', 'not found') !== false
        ) {

            $add = $this->doSync($resident);
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
