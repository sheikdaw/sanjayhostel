<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Resident;
use App\Http\Controllers\EsslController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EsslMonthlyBlock extends Command   // ✅ correct
{
    protected $signature = 'essl:sync-access
                            {--hostel= : Only sync residents of this hostel_id}
                            {--dry-run : Show what would happen without calling the device}
                            {--force   : Sync even if biometric_access already matches}';

    protected $description = 'Sync access based on current-month payment (paid → unblock, unpaid → block)';

    public function handle(): int
    {
        $this->info("Syncing access — checking ALL months for pending dues...");

        $query = Resident::with('hostel')
            ->where('status', 'ACTIVE')
            ->whereNotNull('employee_code');

        if ($hostelId = $this->option('hostel')) {
            $query->where('hostel_id', $hostelId);
        }

        $residents = $query->get();

        if ($residents->isEmpty()) {
            $this->warn('No eligible residents.');
            return self::SUCCESS;
        }

        $this->info("Found {$residents->count()} resident(s).");

        // ✅ Load ALL payments (all months) grouped by resident
        $payments = Payment::whereIn('resident_id', $residents->pluck('id'))
            ->get()
            ->groupBy('resident_id');

        $dryRun = (bool) $this->option('dry-run');
        $force  = (bool) $this->option('force');

        $stats = ['unblocked' => 0, 'blocked' => 0, 'skipped' => 0, 'failed' => 0];

        $bar = $this->output->createProgressBar($residents->count());
        $bar->start();

        $essl = app(EsslController::class);

        foreach ($residents as $resident) {
            $bar->advance();

            $serial = $resident->hostel->biometric_device_id ?? null;
            if (empty($serial)) {
                $stats['skipped']++;
                continue;
            }

            $residentPayments = $payments->get($resident->id, collect());

            // ✅ Pending due irukka? (any month balance > 0)
            $hasPendingDue = $residentPayments->contains(function ($p) {
                return (float) $p->balance_amount > 0;
            });

            // Payment record-ey illa na — due irukku nu consider pannanum
            $hasNoPayments = $residentPayments->isEmpty();

            // Block pannanum if: pending due irukku OR payment-ey illa
            $shouldBlock    = $hasPendingDue || $hasNoPayments;
            $desiredAllowed = !$shouldBlock;

            // Skip if already in desired state
            if (!$force && (bool) $resident->biometric_access === $desiredAllowed) {
                $stats['skipped']++;
                continue;
            }

            if ($dryRun) {
                $this->newLine();
                $this->line(sprintf(
                    '  [DRY] %s (%s) → %s',
                    $resident->name,
                    $resident->employee_code,
                    $shouldBlock ? 'BLOCK' : 'UNBLOCK'
                ));
                $shouldBlock ? $stats['blocked']++ : $stats['unblocked']++;
                continue;
            }

            try {
                $result = $essl->syncResidentAccess($resident, $shouldBlock);

                if (!empty($result['success'])) {
                    $shouldBlock ? $stats['blocked']++ : $stats['unblocked']++;
                } else {
                    $stats['failed']++;
                    Log::warning('essl:sync-access — failed', [
                        'resident_id' => $resident->id,
                        'shouldBlock' => $shouldBlock,
                        'message'     => $result['message'] ?? 'unknown',
                    ]);
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                Log::error('essl:sync-access — exception', [
                    'resident_id' => $resident->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Result', 'Count'],
            [
                ['Unblocked (no due)',    $stats['unblocked']],
                ['Blocked (has due)',     $stats['blocked']],
                ['Skipped (no change)',   $stats['skipped']],
                ['Failed',                $stats['failed']],
            ]
        );

        return self::SUCCESS;
    }
}
