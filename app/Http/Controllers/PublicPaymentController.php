<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Resident;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PublicPaymentController extends Controller
{
    /**
     * ============================================================
     * DISCOUNT SLABS  (edit these numbers anytime)
     * ------------------------------------------------------------
     *  Key = minimum months stayed, Value = discount percentage
     *  Slabs are matched from highest to lowest.
     * ============================================================
     */
    private const DISCOUNT_SLABS = [
        // months_stayed => discount_percentage
        10 => 15,   // 10+ months  → 15%
        5  => 10,   // 5 to <10    → 10%
        1  => 5,    // 1 to <5     → 5%
        0  => 0,    // 0 months    → 0%
    ];

    /**
     * Show public payment lookup page
     * URL: /pay/{encodedHostelId}
     */
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

    /**
     * AJAX: Lookup resident by phone + hostel
     * Returns current month + previous pending details WITH discount slabs
     */
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
        // 1) PREVIOUS PENDING  (with slab discount applied per month)
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
                // Trust saved balance_amount from records
                $due = 0;
                foreach ($payments as $p) {
                    $due += (float) $p->balance_amount;
                }
            } else {
                // No record → compute due for that month WITH slab discount
                $monthsAtThatTime = $joinMonth->diffInMonths($cursor);
                $discountPct      = $this->getDiscountPercentage($monthsAtThatTime);
                $discountAmt      = ((float) $resident->rent_amount) * ($discountPct / 100);
                $due              = max(0, (float) $resident->rent_amount - $discountAmt);
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
        $currentBalance  = 0;
        $currentDiscount = 0;
        $currentFine     = 0;

        if ($currentPayments->count() > 0) {
            foreach ($currentPayments as $p) {
                $currentPaid     += (float) $p->cash_paid_amount + (float) $p->upi_paid_amount;
                $currentBalance  += (float) $p->balance_amount;
                $currentDiscount += (float) $p->discount_amount;
                $currentFine     += (float) $p->fine_amount;
            }
        }

        $currentRent = (float) $resident->rent_amount;

        // ---- Slab discount for current month ----
        $monthsStayed = $joinMonth->diffInMonths($currentMonthStart);
        $slabDiscountPct = $this->getDiscountPercentage($monthsStayed);
        $slabDiscount    = $currentRent * ($slabDiscountPct / 100);

        // Total discount = manual (from payment record) + auto slab discount
        $totalDiscount = $currentDiscount + $slabDiscount;

        // Current due = rent + fine - total discount
        $currentDue = max(0, $currentRent + $currentFine - $totalDiscount);

        // Recompute balance using the corrected due
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

        // ------------------------------------------------------------
        // 3) UPI LINK
        // ------------------------------------------------------------
        $upiId        = $hostel->upi_id ?? null;
        $upiPayeeName = $hostel->upi_payee_name ?? $hostel->hostel_name;

        $upiLink = null;
        if ($upiId && $totalDue > 0) {
            $rawUpiId = $upiId;
            if (strpos($upiId, 'pa=') !== false) {
                preg_match('/pa=([^&]+)/', $upiId, $matches);
                if (isset($matches[1])) {
                    $rawUpiId = urldecode($matches[1]);
                }
            }

            $upiLink = 'upi://pay?' . http_build_query([
                'pa' => $rawUpiId,
                'pn' => $upiPayeeName,
                'am' => number_format($totalDue, 2, '.', ''),
                'cu' => 'INR',
                'tn' => 'Rent - ' . $resident->name,
            ]);
        }

        return response()->json([
            'success'  => true,
            'resident' => [
                'id'            => $resident->id,
                'name'          => $resident->name,
                'code'          => $resident->resident_code,
                'phone'         => $resident->phone,
                'hostel_name'   => $hostel->hostel_name,
                'room_no'       => $resident->room->room_no ?? 'N/A',
                'bed_no'        => $resident->bed->bed_no ?? 'N/A',
                'joining_date'  => Carbon::parse($resident->joining_date)->format('d M Y'),
                'rent_amount'   => $currentRent,
                'months_stayed' => $monthsStayed,
            ],
            'current_month' => [
                'month'             => Carbon::create($currentYear, $currentMonth, 1)->format('F Y'),
                'rent'              => round($currentRent, 2),
                'manual_discount'   => round($currentDiscount, 2),
                'slab_discount_pct' => round($slabDiscountPct, 2),
                'slab_discount'     => round($slabDiscount, 2),
                'total_discount'    => round($totalDiscount, 2),
                'fine'              => round($currentFine, 2),
                'paid'              => round($currentPaid, 2),
                'balance'           => round($currentBalance, 2),
                'due'               => round($currentDue, 2),
                'status'            => $currentStatus,
            ],
            'previous_pending' => [
                'total'  => round($previousPending, 2),
                'months' => $previousMonths,
            ],
            'total_due' => round($totalDue, 2),
            'upi' => [
                'id'         => $upiId,
                'payee_name' => $upiPayeeName,
                'link'       => $upiLink,
            ],
        ]);
    }

    /**
     * Return the applicable discount percentage for a given months-stayed count.
     *
     * Uses self::DISCOUNT_SLABS — matched from highest slab down.
     * Example with defaults:
     *   0 months   → 0%
     *   1–4 months → 5%
     *   5–9 months → 10%
     *   10+ months → 15%
     */
    private function getDiscountPercentage(int $monthsStayed): float
    {
        // Slabs are stored descending by months, so first match wins
        $slabs = self::DISCOUNT_SLABS;
        krsort($slabs); // ensure descending order

        foreach ($slabs as $minMonths => $percent) {
            if ($monthsStayed >= $minMonths) {
                return (float) $percent;
            }
        }

        return 0.0;
    }

    /**
     * Show public index page with QR codes for each hostel
     */
    public function index()
    {
        $user = auth()->user();

        $hostelQuery = Hostel::orderBy('hostel_name');
        if (!$user->isAdmin()) {
            $hostelQuery->whereIn('id', $user->hostel_ids ?? []);
        }

        $hostels = $hostelQuery->get();

        // Build payment link + QR for each hostel
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
