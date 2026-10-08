<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicPaymentController extends Controller
{
    /**
     * Flat date-based discount rules (MUST MATCH PaymentController)
     *   Day 1-5   → ₹250
     *   Day 6-10  → ₹125
     *   Day 11+   → ₹0
     */
    private const DATE_DISCOUNT_1_5  = 250.0;
    private const DATE_DISCOUNT_6_10 = 125.0;

    /* =========================================================
     |  PAGES
     ========================================================= */

    /** GET /pay/{encodedHostelId} */
    public function show(string $encodedHostelId)
    {
        $hostel = $this->resolveHostel($encodedHostelId);
        if (!$hostel) {
            abort(404, 'Invalid payment link');
        }

        return view('public.payment-lookup', compact('hostel', 'encodedHostelId'));
    }

    /** GET /pay/success?order={order_id} */
    public function success(Request $request)
    {
        $orderId = (string) $request->query('order', '');

        // "Success" only if the verified callback really credited this order
        $success = $orderId !== '' && $this->isOrderAlreadyApplied($orderId);

        return view('public.payment-success', compact('success', 'orderId'));
    }

    /** Admin: QR code index */
    public function index()
    {
        $user = auth()->user();

        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }

        $links = $hostelQuery->get()->map(function ($hostel) {
            $url = url('/pay/' . Crypt::encryptString($hostel->id));

            return [
                'id'     => $hostel->id,
                'name'   => $hostel->hostel_name,
                'code'   => $hostel->hostel_code,
                'type'   => $hostel->hostel_type,
                'status' => $hostel->status,
                'phone'  => $hostel->phone,
                'upi_id' => $hostel->upi_id,
                'url'    => $url,
                'qr'     => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($url),
            ];
        });

        return view('public.index', compact('links'));
    }

    /* =========================================================
     |  LOOKUP (AJAX)
     ========================================================= */

    public function lookup(Request $request, string $encodedHostelId)
    {
        $hostel = $this->resolveHostel($encodedHostelId);
        if (!$hostel) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $request->validate(['phone' => 'required|string|min:10|max:15']);

        $resident = $this->findResident($hostel->id, $request->phone);
        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'No active resident found with this mobile number in this hostel.',
            ]);
        }

        $dues = $this->calculateDues($resident, Carbon::now());

        return response()->json([
            'success'  => true,
            'resident' => [
                'id'           => $resident->id,
                'name'         => $resident->name,
                'code'         => $resident->resident_code,
                'phone'        => $resident->phone,
                'hostel_name'  => $hostel->hostel_name,
                'room_no'      => $resident->room->room_no ?? 'N/A',
                'bed_no'       => $resident->bed->bed_no ?? 'N/A',
                'joining_date' => Carbon::parse($resident->joining_date)->format('d M Y'),
                'rent_amount'  => (float) $resident->rent_amount,
            ],
            'current_month'    => $dues['current_month'],
            'previous_pending' => $dues['previous_pending'],
            'total_due'        => $dues['total_due'],
            'can_pay'          => $dues['total_due'] > 0,
        ]);
    }

    /* =========================================================
     |  INITIATE PAYMENT  (POST /pay/{enc}/initiate)
     ========================================================= */

    public function initiate(Request $request, string $encodedHostelId)
    {
        $hostel = $this->resolveHostel($encodedHostelId);
        if (!$hostel) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $request->validate(['phone' => 'required|string|min:10|max:15']);

        $resident = $this->findResident($hostel->id, $request->phone);
        if (!$resident) {
            return response()->json(['success' => false, 'message' => 'Resident not found.'], 404);
        }

        // Amount is ALWAYS computed on the server, never taken from the browser
        $dues = $this->calculateDues($resident, Carbon::now());

        if ($dues['total_due'] <= 0) {
            return response()->json(['success' => false, 'message' => 'No balance due. Nothing to pay.']);
        }

        // Order id carries resident + start time (no database needed)
        $orderId = 'PG-' . $resident->id . '-' . now()->format('ymdHis') . '-' . strtoupper(Str::random(4));

        $gateway = $this->buildGatewayRequest($orderId, $dues['total_due'], $resident);

        // Flat contract the blade JS expects.
        // If your Axis integration returns a hosted page URL, set mode='redirect'
        // and put that URL in redirect_url. Otherwise the JS auto-submits the
        // signed form in `gateway`.
        return response()->json([
            'success'      => true,
            'order_id'     => $orderId,
            'mode'         => 'redirect',   // 'redirect' | 'upi_link' | 'qr'
            'redirect_url' => null,         // fill if Axis gives a hosted page URL
            'upi_link'     => null,         // fill if Axis gives a UPI intent string
            'qr_code'      => null,         // fill if Axis returns a QR image
            'gateway'      => $gateway,     // { url, method, fields[] }
        ]);
    }

    /* =========================================================
     |  CALLBACK  (POST /pay/axis/callback  - called by the bank)
     ========================================================= */

    public function callback(Request $request)
    {
        $data = $request->all();
        Log::info('Axis callback received', $data);

        // 1) Verify the bank's signature
        $parsed = $this->verifyCallback($data);
        if (!$parsed) {
            Log::warning('Axis callback signature invalid', $data);
            abort(400, 'Invalid signature');
        }

        $orderId = (string) $parsed['order_id'];

        // 2) Read resident + start time from the order id
        $order = $this->parseOrderId($orderId);
        if (!$order) {
            abort(404, 'Unknown order');
        }

        $resident = Resident::with('hostel')->find($order['resident_id']);
        if (!$resident) {
            abort(404, 'Unknown resident');
        }

        // 3) Apply (only if bank says success)
        $applied = false;

        if ($parsed['success'] && $parsed['amount'] > 0) {
            // Lock so two simultaneous callbacks cannot both credit
            $lock = Cache::lock('axis-order-' . $orderId, 30);

            if ($lock->get()) {
                try {
                    DB::transaction(function () use ($orderId, $order, $resident, $parsed, &$applied) {
                        if ($this->isOrderAlreadyApplied($orderId)) {
                            return;     // bank retry / page refresh
                        }

                        $applied = $this->applyPaymentToLedger(
                            $resident,
                            $orderId,
                            (float) $parsed['amount'],
                            $parsed['gateway_txn_id'],
                            $order['started_at']
                        );
                    });
                } finally {
                    $lock->release();
                }
            }
        } else {
            Log::info('Axis payment not successful', ['order' => $orderId, 'data' => $data]);
        }

        // 4) Unblock AFTER the DB commit (never inside the transaction)
        if ($applied) {
            $this->syncAccessAfterPayment($resident);
        }

        // Browser redirect (user) vs server-to-server call (bank)
        if (!$request->expectsJson()) {
            return redirect()->route('public.payment.success', ['order' => $orderId]);
        }

        return response()->json(['success' => true]);
    }

    /* =========================================================
     |  AXIS BANK SPECIFIC  (ADJUST THESE TWO TO AXIS'S DOCUMENT)
     ========================================================= */

    /**
     * Build the data the browser posts to Axis checkout.
     * Field names + checksum rule come from your Axis integration document.
     */
    private function buildGatewayRequest(string $orderId, float $amount, Resident $resident): array
    {
        $fields = [
            'merchant_id'    => config('services.axis.merchant_id'),
            'order_id'       => $orderId,
            'amount'         => number_format($amount, 2, '.', ''),
            'currency'       => 'INR',
            'customer_name'  => $resident->name,
            'customer_phone' => preg_replace('/\D/', '', (string) $resident->phone),
            'return_url'     => route('public.payment.callback'),
        ];

        // PLACEHOLDER signing: replace with Axis's exact checksum/encryption method
        $fields['signature'] = $this->sign($fields);

        return [
            'url'    => config('services.axis.payment_url'),
            'method' => 'POST',
            'fields' => $fields,
        ];
    }

    /**
     * Verify the callback from Axis.
     * Return null if invalid, otherwise a normalised array.
     */
    private function verifyCallback(array $data): ?array
    {
        $received = (string) ($data['signature'] ?? '');
        $payload  = collect($data)->except(['signature', '_token'])->all();

        // PLACEHOLDER: use Axis's exact verification method
        if (!hash_equals($this->sign($payload), $received)) {
            return null;
        }

        // PLACEHOLDER field names: map to Axis's response fields
        return [
            'order_id'       => $data['order_id'] ?? null,
            'amount'         => (float) ($data['amount'] ?? 0),
            'gateway_txn_id' => $data['txn_id'] ?? null,
            'success'        => strtoupper((string) ($data['status'] ?? '')) === 'SUCCESS',
        ];
    }

    private function sign(array $fields): string
    {
        ksort($fields);
        return hash_hmac('sha256', http_build_query($fields), (string) config('services.axis.secret_key'));
    }

    /* =========================================================
     |  ORDER ID HELPERS (stateless)
     ========================================================= */

    /** PG-{residentId}-{yymmddHHMMSS}-{RAND} */
    private function parseOrderId(string $orderId): ?array
    {
        if (!preg_match('/^PG-(\d+)-(\d{12})-[A-Z0-9]{4}$/', $orderId, $m)) {
            return null;
        }

        try {
            $startedAt = Carbon::createFromFormat('ymdHis', $m[2]);
        } catch (\Exception $e) {
            return null;
        }

        return [
            'resident_id' => (int) $m[1],
            'started_at'  => $startedAt,
        ];
    }

    /** An order is "applied" if any payment row already carries its id in remark */
    private function isOrderAlreadyApplied(string $orderId): bool
    {
        return Payment::where('remark', 'LIKE', '%Online ' . $orderId . '%')->exists();
    }

    /* =========================================================
     |  DUES CALCULATION (single source of truth)
     ========================================================= */

    /**
     * @param Carbon $asOf  "today" for this calculation. For a callback this is
     *                      the time the payment was STARTED, so the date
     *                      discount the resident saw is the one they get.
     */
    private function calculateDues(Resident $resident, Carbon $asOf): array
    {
        $currentMonth = (int) $asOf->month;
        $currentYear  = (int) $asOf->year;
        $rent         = (float) $resident->rent_amount;

        $allocations     = [];   // oldest month first
        $previousMonths  = [];
        $previousPending = 0;

        $cursor            = Carbon::parse($resident->joining_date)->startOfMonth();
        $currentMonthStart = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();

        $paymentsByMonth = Payment::where('resident_id', $resident->id)->get()
            ->groupBy(fn($p) => $p->year . '-' . str_pad($p->month, 2, '0', STR_PAD_LEFT));

        // ── Previous months (no date discount) ──
        while ($cursor->lt($currentMonthStart)) {
            $payments = $paymentsByMonth[$cursor->format('Y-m')] ?? collect();

            $due = $payments->count() > 0
                ? (float) $payments->sum('balance_amount')
                : $rent;

            if ($due > 0) {
                $previousPending += $due;
                $previousMonths[] = ['label' => $cursor->format('F Y'), 'amount' => round($due, 2)];
                $allocations[]    = [
                    'month'    => (int) $cursor->month,
                    'year'     => (int) $cursor->year,
                    'amount'   => round($due, 2),
                    'discount' => 0,
                    'rent'     => $rent,
                    'fine'     => 0,
                ];
            }

            $cursor->addMonth();
        }

        // ── Current month ──
        $currentPayments = $paymentsByMonth[$currentMonthStart->format('Y-m')] ?? collect();

        $paid     = 0;
        $discount = 0;
        $fine     = 0;
        foreach ($currentPayments as $p) {
            $paid     += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
            $discount += (float) $p->discount_amount;
            $fine     += (float) $p->fine_amount;
        }

        if ($currentPayments->count() > 0) {
            $dateDiscount  = 0;
            $totalDiscount = $discount;
        } else {
            $dateDiscount  = $this->getDateBasedDiscount($asOf);
            $totalDiscount = $discount + $dateDiscount;
        }

        $currentDue     = max(0, $rent + $fine - $totalDiscount);
        $currentBalance = $currentPayments->count() > 0
            ? max(0, $currentDue - $paid)
            : $currentDue;

        if ($currentPayments->count() === 0) {
            $status = 'UNPAID';
        } elseif ($currentBalance > 0) {
            $status = 'PARTIAL';
        } else {
            $status = 'PAID';
        }

        if ($currentBalance > 0) {
            $allocations[] = [
                'month'    => $currentMonth,
                'year'     => $currentYear,
                'amount'   => round($currentBalance, 2),
                'discount' => round($totalDiscount, 2),   // used only if no row exists yet
                'rent'     => $rent,
                'fine'     => round($fine, 2),
            ];
        }

        return [
            'current_month' => [
                'month'           => $currentMonthStart->format('F Y'),
                'rent'            => round($rent, 2),
                'manual_discount' => round($discount, 2),
                'date_discount'   => round($dateDiscount, 2),
                'total_discount'  => round($totalDiscount, 2),
                'fine'            => round($fine, 2),
                'paid'            => round($paid, 2),
                'balance'         => round($currentBalance, 2),
                'due'             => round($currentDue, 2),
                'status'          => $status,
            ],
            'previous_pending' => [
                'total'  => round($previousPending, 2),
                'months' => $previousMonths,
            ],
            'total_due'   => round($previousPending + $currentBalance, 2),
            'allocations' => $allocations,
        ];
    }

    /* =========================================================
     |  APPLY SUCCESSFUL PAYMENT TO THE PAYMENTS TABLE
     ========================================================= */

    /**
     * Distributes the bank-confirmed amount over the dues (oldest month first).
     * Returns true if anything was credited.
     */
    private function applyPaymentToLedger(
        Resident $resident,
        string $orderId,
        float $paidAmount,
        ?string $gatewayTxnId,
        Carbon $startedAt
    ): bool {
        $dues      = $this->calculateDues($resident, $startedAt);
        $remaining = round($paidAmount, 2);
        $credited  = false;

        foreach ($dues['allocations'] as $alloc) {
            if ($remaining <= 0) {
                break;
            }

            $credit = min($remaining, (float) $alloc['amount']);
            if ($credit <= 0) {
                continue;
            }

            $payment = Payment::where('resident_id', $resident->id)
                ->where('month', $alloc['month'])
                ->where('year', $alloc['year'])
                ->lockForUpdate()
                ->first();

            if ($payment) {
                $credit  = min($credit, (float) $payment->balance_amount);
                $balance = max(0, (float) $payment->balance_amount - $credit);
                $cash    = (float) $payment->cash_paid_amount;
                $upi     = (float) $payment->upi_paid_amount + $credit;

                $payment->update([
                    'upi_paid_amount' => $upi,
                    'balance_amount'  => $balance,
                    'payment_type'    => $cash > 0 ? 'both' : 'upi',
                    'transaction_id'  => $gatewayTxnId ?: $orderId,
                    'status'          => $balance > 0 ? 'PARTIAL' : 'PAID',
                    'remark'          => trim(($payment->remark ? $payment->remark . ' | ' : '') . 'Online ' . $orderId),
                ]);
            } else {
                $payable = max(0, (float) $alloc['rent'] + (float) $alloc['fine'] - (float) $alloc['discount']);
                $balance = max(0, $payable - $credit);

                do {
                    $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                } while (Payment::where('receipt_no', $receiptNo)->exists());

                Payment::create([
                    'resident_id'      => $resident->id,
                    'month'            => $alloc['month'],
                    'year'             => $alloc['year'],
                    'receipt_no'       => $receiptNo,
                    'rent_amount'      => $alloc['rent'],
                    'discount_amount'  => $alloc['discount'],
                    'fine_amount'      => $alloc['fine'],
                    'cash_paid_amount' => 0,
                    'upi_paid_amount'  => $credit,
                    'balance_amount'   => $balance,
                    'payment_date'     => now()->toDateString(),
                    'transaction_id'   => $gatewayTxnId ?: $orderId,
                    'payment_type'     => 'upi',
                    'remark'           => 'Online ' . $orderId,
                    'status'           => $balance > 0 ? 'PARTIAL' : 'PAID',
                ]);
            }

            $remaining = round($remaining - $credit, 2);
            $credited  = true;
        }

        if ($remaining > 0) {
            // Bank took more than was due (e.g. admin recorded cash meanwhile)
            Log::warning('Axis surplus not allocated', [
                'order'    => $orderId,
                'resident' => $resident->id,
                'surplus'  => $remaining,
            ]);
        }

        return $credited;
    }

    /* =========================================================
     |  AUTO UNBLOCK / BLOCK  (same rule as admin PaymentController)
     ========================================================= */

    private function syncAccessAfterPayment(?Resident $resident): void
    {
        if (!$resident) {
            return;
        }

        try {
            $resident->refresh();
            $resident->load('hostel');

            if ($resident->status !== 'ACTIVE') {
                return;
            }
            if (!$resident->hostel || !$resident->hostel->biometric_device_id) {
                return;
            }

            $now      = now();
            $prevDate = $now->copy()->subMonthNoOverflow();

            $currentPaid = $this->isMonthFullyPaid($resident->id, (int) $now->month, (int) $now->year);

            // Joined this month → previous month not required
            $joinDate        = $resident->joining_date ? Carbon::parse($resident->joining_date) : null;
            $joinedThisMonth = $joinDate
                && $joinDate->year === (int) $now->year
                && $joinDate->month === (int) $now->month;

            $prevPaid = $joinedThisMonth
                ? true
                : $this->isMonthFullyPaid($resident->id, (int) $prevDate->month, (int) $prevDate->year);

            $shouldUnblock = $currentPaid && $prevPaid;

            Log::info('Public payment access sync', [
                'resident_id'    => $resident->id,
                'current_paid'   => $currentPaid,
                'prev_paid'      => $prevPaid,
                'should_unblock' => $shouldUnblock,
            ]);

            /** @var \App\Http\Controllers\EsslController $essl */
            $essl = app(EsslController::class);
            $essl->syncResidentAccess($resident, !$shouldUnblock);   // true = BLOCK, false = UNBLOCK
        } catch (\Throwable $e) {
            // Payment is already saved; never fail the payment because of the device
            Log::error('Public syncAccessAfterPayment failed', [
                'resident_id' => $resident->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    private function isMonthFullyPaid(int $residentId, int $month, int $year): bool
    {
        $payment = Payment::where('resident_id', $residentId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payment) {
            return false;
        }

        $totalPaid = (float) $payment->cash_paid_amount + (float) $payment->upi_paid_amount;

        return (float) $payment->balance_amount <= 0 && $totalPaid > 0;
    }

    /* =========================================================
     |  HELPERS
     ========================================================= */

    private function resolveHostel(string $encodedHostelId): ?Hostel
    {
        try {
            return Hostel::find(Crypt::decryptString($encodedHostelId));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findResident(int|string $hostelId, string $rawPhone): ?Resident
    {
        $phone  = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = substr($phone, -10);

        return Resident::with(['room', 'bed'])
            ->where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->where(function ($q) use ($phone, $last10) {
                $q->where('phone', 'LIKE', "%{$last10}%")
                  ->orWhere('phone', $phone);
            })
            ->first();
    }

    private function getDateBasedDiscount(?Carbon $asOf = null): float
    {
        $day = (int) ($asOf ?? Carbon::now())->day;

        if ($day >= 1 && $day <= 5)  return self::DATE_DISCOUNT_1_5;
        if ($day >= 6 && $day <= 10) return self::DATE_DISCOUNT_6_10;

        return 0.0;
    }
}