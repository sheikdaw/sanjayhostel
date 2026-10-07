<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Resident;
use App\Services\EsslService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EsslMonthlyBlock extends Command
{
    protected $signature = 'essl:sync-access
                            {--hostel= : Only sync residents of this hostel_id}
                            {--dry-run : Show what would happen without calling the device}
                            {--force   : Sync even if biometric_access already matches}
                            {--limit=0 : Limit residents processed (0 = no limit)}';

    protected $description = 'Sync biometric access based on pending dues';

    public function handle(EsslService $essl): int
    {
        set_time_limit(0);

        // CLI options
        $hostelId = $this->option('hostel');
        $dryRun   = (bool) $this->option('dry-run');
        $force    = (bool) $this->option('force');
        $limit    = (int)  $this->option('limit');

        $this->info('Checking ACTIVE residents...');

        // ─── 1. ACTIVE residents eduthuko ───
        $query = Resident::with('hostel')
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code');

        if ($hostelId) $query->where('hostel_id', $hostelId);
        if ($limit)    $query->limit($limit);

        $residents = $query->get();

        if ($residents->isEmpty()) {
            $this->warn('No eligible residents.');
            return self::SUCCESS;
        }

        // Ellaa payments-um oru query-la load pannu
        $allPayments = Payment::whereIn('resident_id', $residents->pluck('id'))
            ->get()
            ->groupBy('resident_id');

        // Simple counters
        $unblocked = 0;
        $blocked   = 0;
        $skipped   = 0;
        $failed    = 0;

        $this->info("Found {$residents->count()} resident(s).");

        foreach ($residents as $resident) {

            // Device serial check
            $serial = $resident->hostel->biometric_device_id ?? null;
            if (empty($serial)) {
                $skipped++;
                continue;
            }

            // Andha resident-oda payments
            $payments = $allPayments->get($resident->id, collect());

            // Payment record illa → skip
            if ($payments->isEmpty()) {
                $skipped++;
                continue;
            }

            // Due irukkaa? (edhavadhu payment-la balance > 0)
            $hasDue = false;
            foreach ($payments as $p) {
                if ((float) $p->balance_amount > 0) {
                    $hasDue = true;
                    break;
                }
            }

            // Due irundha BLOCK, illa na UNBLOCK
            $shouldBlock = $hasDue;

            // Already same state-la irundha skip (unless --force)
            if (!$force && (bool) $resident->biometric_access === !$shouldBlock) {
                $skipped++;
                continue;
            }

            // Dry-run: print mattum
            if ($dryRun) {
                $this->line("  [DRY] {$resident->name} → " . ($shouldBlock ? 'BLOCK' : 'UNBLOCK'));
                $shouldBlock ? $blocked++ : $unblocked++;
                continue;
            }

            // Real API call
            try {
                $result = $essl->blockUnblock(
                    (string) $resident->employee_code,
                    $resident->name,
                    $serial,
                    $shouldBlock
                );

                if (!empty($result['success'])) {
                    $resident->biometric_access = !$shouldBlock;
                    $resident->last_sync_at     = now();

                    if ($shouldBlock) {
                        $resident->access_disabled_at = now();
                        $blocked++;
                    } else {
                        $resident->access_enabled_at = now();
                        $unblocked++;
                    }
                    $resident->save();
                } else {
                    $failed++;
                    Log::warning('essl:sync-access failed', [
                        'resident_id' => $resident->id,
                        'message'     => $result['message'] ?? 'unknown',
                    ]);
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('essl:sync-access exception', [
                    'resident_id' => $resident->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        // ─── 2. Exited residents (device-la innum active) → BLOCK ───
        $this->info('Checking exited residents...');

        $exited = Resident::with('hostel')
            ->where('status', '!=', 'ACTIVE')
            ->where('biometric_access', true)
            ->whereNotNull('employee_code')
            ->when($hostelId, fn($q) => $q->where('hostel_id', $hostelId))
            ->get();

        foreach ($exited as $resident) {

            $serial = $resident->hostel->biometric_device_id ?? null;
            if (empty($serial)) continue;

            if ($dryRun) {
                $this->line("  [DRY] EXIT-BLOCK {$resident->name}");
                $blocked++;
                continue;
            }

            try {
                $r = $essl->blockUnblock(
                    (string) $resident->employee_code,
                    $resident->name,
                    $serial,
                    true
                );

                if (!empty($r['success'])) {
                    $resident->update([
                        'biometric_access'   => false,
                        'access_disabled_at' => now(),
                    ]);
                    $blocked++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('essl exit-block exception', [
                    'resident_id' => $resident->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        // ─── Summary ───
        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Unblocked', $unblocked],
                ['Blocked',   $blocked],
                ['Skipped',   $skipped],
                ['Failed',    $failed],
            ]
        );

        return self::SUCCESS;
    }
}
// php artisan essl:sync-access --dry-run
// php artisan essl:sync-access --hostel=5 --limit=10
// php artisan essl:sync-access --force
// php artisan essl:sync-access
