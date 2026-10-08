<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Resident;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayManager;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    public function __construct(private PaymentService $paymentService) {}

    /* =========================================================
     |  SHOW — Public payment lookup page
     |  URL: /pay/{encodedHostelId}
     ========================================================= */

    public function show(string $encodedHostelId)
    {
        try {
            $hostelId = Crypt::decryptString($encodedHostelId);
        } catch (\Exception $e) {
            abort(404, 'Invalid payment link');
        }

        $hostel = Hostel::find($hostelId);
        if (!$hostel) {
            abort(404, 'Hostel not found');
        }

        return view('public.payment-lookup', compact('hostel', 'encodedHostelId'));
    }

    /* =========================================================
     |  DATE DISCOUNT helper
     ========================================================= */

    private function getDateBasedDiscount(?Carbon $asOf = null): float
    {
        $day = (int) ($asOf ?? Carbon::now())->day;

        if ($day >= 1 && $day <= 5) {
            return self::DATE_DISCOUNT_1_5;
        }
        if ($day >= 6 && $day <= 10) {
            return self::DATE_DISCOUNT_6_10;
        }
        return 0.0;
    }

    /* =========================================================
     |  LOOKUP — AJAX: Find resident by phone, return dues
     ========================================================= */

    public function lookup(Request $request, string $encodedHostelId)
    {
        try {
            $hostelId = Crypt::decryptString($encodedHostelId);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $hostel = Hostel::find($hostelId);
        if (!$hostel) {
            return response()->json(['success' => false, 'message' => 'Hostel not found'], 404);
        }

        // Search resident by phone (last 10 digits)
        $phone  = preg_replace('/[^0-9]/', '', $request->phone);
        $last10 = substr($phone, -10);

        $resident = Resident::with(['room', 'bed'])
            ->where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->where(function ($q) use ($phone, $last10) {
                $q->where('phone', 'LIKE', "%{$last10}%")
                  ->orWhere('phone', $phone);
            })
            ->first();

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'No active resident found with this mobile number in this hostel.',
            ]);
        }

        // Current month/year
        $now          = Carbon::now();
        $currentMonth = (int) $now->month;
        $currentYear  = (int) $now->year;

        // ------------------------------------------------------------
        // 1) PREVIOUS PENDING (no date discount for old months)
        // ------------------------------------------------------------
        $previousPending = 0;
        $previousMonths  = [];

        $joinMonth         = Carbon::parse($resident->joining_date)->startOfMonth();
        $currentMonthStart = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();

        $cursor = $joinMonth->copy();
        while ($cursor->lt($currentMonthStart)) {
            $payments = Payment::where('resident_id', $resident->id)
                ->where('month', $cursor->month)
                ->where('year', $cursor->year)
                ->get();

            if ($payments->count() > 0) {
                $due = 0;
                foreach ($payments as $p) {
                    $due += (float) $p->balance_amount;
                }
            } else {
                $due = (float) $resident->rent_amount;
            }

            if ($due > 0) {
                $previousPending += $due;
                $previousMonths[] = [
                    'label'  => $cursor->format('F Y'),
                    'amount' => round($due, 2),
                ];
            }

            $cursor->addMonth();
        }

        // ------------------------------------------------------------
        // 2) CURRENT MONTH
        // ------------------------------------------------------------
        $currentPayments = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get();

        $currentPaid     = 0;
        $currentDiscount = 0;
        $currentFine     = 0;

        if ($currentPayments->count() > 0) {
            foreach ($currentPayments as $p) {
                $currentPaid     += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                $currentDiscount += (float) $p->discount_amount;
                $currentFine     += (float) $p->fine_amount;
            }
        }

        $currentRent = (float) $resident->rent_amount;

        // ---- Date-based flat discount ----
        if ($currentPayments->count() > 0) {
            $dateDiscount  = 0;
            $totalDiscount = $currentDiscount;
        } else {
            $dateDiscount  = $this->getDateBasedDiscount();
            $totalDiscount = $currentDiscount + $dateDiscount;
        }

        $currentDue = max(0, $currentRent + $currentFine - $totalDiscount);

        if ($currentPayments->count() > 0) {
            $currentBalance = max(0, $currentDue - $currentPaid);
        } else {
            $currentBalance = $currentDue;
        }

        // Status
        if ($currentPayments->count() === 0) {
            $currentStatus = 'UNPAID';
        } elseif ($currentBalance > 0) {
            $currentStatus = 'PARTIAL';
        } else {
            $currentStatus = 'PAID';
        }

        $totalDue = $previousPending + $currentBalance;

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
                'rent_amount'  => $currentRent,
            ],
            'current_month' => [
                'month'           => Carbon::create($currentYear, $currentMonth, 1)->format('F Y'),
                'rent'            => round($currentRent, 2),
                'manual_discount' => round($currentDiscount, 2),
                'date_discount'   => round($dateDiscount, 2),
                'total_discount'  => round($totalDiscount, 2),
                'discount'        => round($totalDiscount, 2),
                'fine'            => round($currentFine, 2),
                'paid'            => round($currentPaid, 2),
                'balance'         => round($currentBalance, 2),
                'due'             => round($currentDue, 2),
                'status'          => $currentStatus,
            ],
            'previous_pending' => [
                'total'  => round($previousPending, 2),
                'months' => $previousMonths,
            ],
            'total_due' => round($totalDue, 2),
        ]);
    }

    /* =========================================================
     |  INITIATE — Gateway choose pannum
     ========================================================= */

    public function initiate(Request $request, string $encodedHostelId)
    {
        try {
            $hostelId = Crypt::decryptString($encodedHostelId);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid link'], 400);
        }

        $request->validate([
            'resident_id' => 'required|integer|exists:residents,id',
            'amount'      => 'required|numeric|min:1',
        ]);

        $hostel   = Hostel::findOrFail($hostelId);
        $resident = Resident::findOrFail($request->resident_id);

        // Security: resident must belong to this hostel
        if ((int) $resident->hostel_id !== (int) $hostel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Resident does not belong to this hostel.',
            ], 403);
        }

        $amount  = (float) $request->amount;
        $gateway = PaymentGatewayManager::driver();

        $result = $gateway->createOrder($resident, $hostel, $amount, [
            'resident_id' => $resident->id,
            'hostel_id'   => $hostel->id,
        ]);

        $result['gateway'] = $gateway->getName();

        return response()->json($result);
    }

    /* =========================================================
     |  WEBHOOK — Gateway server-to-server callback
     ========================================================= */

    public function webhook(Request $request, string $gateway)
    {
        $rawBody = $request->getContent();
        $headers = $request->headers->all();

        $driver = match ($gateway) {
            'axis'  => new \App\Services\Payment\AxisBankGateway(),
            default => null,
        };

        if (!$driver) {
            return response()->json(['status' => 'unknown gateway'], 400);
        }

        // Verify signature
        if (!$driver->verifyWebhook($headers, $rawBody)) {
            Log::warning("Webhook signature failed: {$gateway}");
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $payload = json_decode($rawBody, true) ?? [];
        $parsed  = $driver->parseWebhook($payload);

        if (empty($parsed['success'])) {
            return response()->json(['status' => 'ignored']);
        }

        // Find resident
        $resident = null;
        if (!empty($parsed['resident_id'])) {
            $resident = Resident::find($parsed['resident_id']);
        }

        if (!$resident) {
            Log::warning('Webhook: resident not found', $parsed);
            return response()->json(['status' => 'resident not found']);
        }

        // Record payment + auto block/unblock
        $this->paymentService->recordSuccessfulPayment(
            $resident,
            (float) $parsed['amount'],
            $parsed['transaction_id'] ?: $parsed['order_id'],
            $gateway,
            $parsed
        );

        return response()->json(['status' => 'ok']);
    }

    /* =========================================================
     |  CALLBACK — User returns from Axis page
     |  NOTE: No {gateway} param — route already fixed to /axis
     ========================================================= */

    public function callback(Request $request)
    {
        Log::info('Payment callback received', [
            'query' => $request->query(),
        ]);

        return redirect()->route('public.payment.success');
    }

    /* =========================================================
     |  CANCEL — User cancelled payment on Axis page
     ========================================================= */

    public function cancel(Request $request)
    {
        Log::info('Payment cancelled by user', [
            'query' => $request->query(),
        ]);

        return redirect()
            ->route('public.payment.success')
            ->with('error', 'Payment was cancelled.');
    }

    /* =========================================================
     |  SUCCESS page
     ========================================================= */

    public function success()
    {
        return view('public.payment-success');
    }

    /* =========================================================
     |  INDEX — Payment links list (admin panel)
     ========================================================= */

    public function index()
    {
        $user = auth()->user();

        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }

        $hostels = $hostelQuery->get();

        $links = $hostels->map(function ($hostel) {
            $encodedId = Crypt::encryptString($hostel->id);
            $url       = url('/pay/' . $encodedId);

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
}