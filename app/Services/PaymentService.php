<?php

namespace App\Services;

use App\Http\Controllers\EsslController;
use App\Models\Payment;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Save payment record when gateway confirms success.
     * Auto block/unblock based on full payment.
     */
    public function recordSuccessfulPayment(
        Resident $resident,
        float $amount,
        string $transactionId,
        string $gateway,
        array $meta = []
    ): ?Payment {
        try {
            $now = Carbon::now();

            // Idempotency — already recorded?
            $existing = Payment::where('transaction_id', $transactionId)->first();
            if ($existing) {
                Log::info('Payment already recorded', [
                    'txn'         => $transactionId,
                    'resident_id' => $resident->id,
                ]);
                return $existing;
            }

            $totalDue = $this->calculateTotalDue($resident);

            if ($totalDue <= 0) {
                Log::info('No dues for resident', ['resident_id' => $resident->id]);
                return null;
            }

            $receiptNo = 'PAY-' . strtoupper(Str::random(10));

            $payment = Payment::create([
                'resident_id'      => $resident->id,
                'month'            => $now->month,
                'year'             => $now->year,
                'rent_amount'      => $resident->rent_amount,
                'discount_amount'  => 0,
                'fine_amount'      => 0,
                'cash_paid_amount' => 0,
                'upi_paid_amount'  => $amount,
                'balance_amount'   => max(0, $totalDue - $amount),
                'payment_date'     => $now,
                'transaction_id'   => $transactionId,
                'payment_type'     => 'upi',
                'remark'           => strtoupper($gateway) . ': ' . $transactionId,
                'status'           => $amount >= $totalDue ? 'PAID' : 'PARTIAL',
                'receipt_no'       => $receiptNo,
            ]);

            // 🔑 Auto block/unblock
            $this->autoSyncAccess($resident);

            return $payment;

        } catch (\Throwable $e) {
            Log::error('recordSuccessfulPayment failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate total due (current + previous pending).
     */
    private function calculateTotalDue(Resident $resident): float
    {
        $now          = Carbon::now();
        $currentMonth = (int) $now->month;
        $currentYear  = (int) $now->year;

        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        $currentDue = $currentPayment
            ? (float) $currentPayment->balance_amount
            : (float) $resident->rent_amount;

        $previousDue = 0;
        $joinMonth   = Carbon::parse($resident->joining_date)->startOfMonth();
        $cursor      = $joinMonth->copy();

        while ($cursor->lt($now->copy()->startOfMonth())) {
            $p = Payment::where('resident_id', $resident->id)
                ->where('month', $cursor->month)
                ->where('year', $cursor->year)
                ->first();

            if ($p) {
                $previousDue += (float) $p->balance_amount;
            } else {
                $previousDue += (float) $resident->rent_amount;
            }

            $cursor->addMonth();
        }

        return $currentDue + $previousDue;
    }

    /**
     * Auto block/unblock based on full payment status.
     */
    private function autoSyncAccess(Resident $resident): void
    {
        try {
            $resident->refresh();
            $resident->load('hostel');

            if ($resident->status !== 'ACTIVE') return;
            if (!$resident->hostel || !$resident->hostel->biometric_device_id) return;

            $essl = app(EsslController::class);

            $now          = now();
            $currentMonth = (int) $now->month;
            $currentYear  = (int) $now->year;

            $prevDate  = $now->copy()->subMonthNoOverflow();
            $prevMonth = (int) $prevDate->month;
            $prevYear  = (int) $prevDate->year;

            $currentPaid = $this->isMonthFullyPaid($resident, $currentMonth, $currentYear);

            $joinDate  = $resident->joining_date ? Carbon::parse($resident->joining_date) : null;
            $checkPrev = !($joinDate && $joinDate->year === $currentYear && $joinDate->month === $currentMonth);

            $prevPaid = $checkPrev
                ? $this->isMonthFullyPaid($resident, $prevMonth, $prevYear)
                : true;

            if ($currentPaid && $prevPaid) {
                $essl->syncResidentAccess($resident, false); // UNBLOCK
            } else {
                $essl->syncResidentAccess($resident, true);  // BLOCK
            }
        } catch (\Throwable $e) {
            Log::error('autoSyncAccess failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private function isMonthFullyPaid(Resident $resident, int $month, int $year): bool
    {
        $p = Payment::where('resident_id', $resident->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$p) return false;

        $paid = (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
        return (float) $p->balance_amount <= 0 && $paid > 0;
    }
}