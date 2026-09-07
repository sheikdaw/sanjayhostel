<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Calculate discount based on payment date
     *
     * @param string $paymentDate
     * @return int
     */
    private function calculateDiscount($paymentDate)
    {
        $day = date('j', strtotime($paymentDate));

        if ($day <= 5) {
            return 250;  // ₹250 discount for 1st-5th
        } elseif ($day <= 10) {
            return 125;  // ₹125 discount for 6th-10th
        } else {
            return 0;    // No discount after 10th
        }
    }

    /**
     * Check if resident is eligible for discount
     * Eligibility: No previous pending AND full payment
     */
    private function isEligibleForDiscount($residentId, $month, $year, $totalPaid, $rentAmount)
    {
        // Check if there's any previous pending
        $previousPending = $this->getPreviousPending($residentId, $month, $year);
        $hasPreviousPending = $previousPending > 0;

        // Check if payment covers full rent
        $amountForCurrentMonth = $totalPaid - $previousPending;
        $canCoverFullRent = $amountForCurrentMonth >= $rentAmount;

        // Eligible only if NO previous pending AND can cover full rent
        return !$hasPreviousPending && $canCoverFullRent;
    }

    /**
     * Get previous pending payments
     */
    private function getPreviousPending($residentId, $month, $year)
    {
        return Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)
                         ->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->sum('balance_amount');
    }

    /**
     * Get previous pending payments with details
     */
    private function getPreviousPendingDetails($residentId, $month, $year)
    {
        return Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)
                         ->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
    }

    /**
     * Get previous months list
     */
    private function getPreviousMonthsList($residentId, $month, $year)
    {
        return Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)
                         ->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($p) {
                return date('F Y', mktime(0,0,0,$p->month,1,$p->year));
            })
            ->implode(', ');
    }

    /**
     * Calculate full payment details
     */
    private function calculatePaymentDetails($resident, $month, $year, $paymentDate)
    {
        $rent = $resident->rent_amount ?? 0;
        $discount = $this->calculateDiscount($paymentDate);
        $currentDue = $rent - $discount;
        $previousPending = $this->getPreviousPending($resident->id, $month, $year);
        $totalDue = $currentDue + $previousPending;
        $previousMonths = $this->getPreviousMonthsList($resident->id, $month, $year);

        return [
            'rent' => $rent,
            'discount' => $discount,
            'discount_type' => $discount > 0 ? ($discount == 250 ? 'Early Bird (1st-5th)' : 'Early Payment (6th-10th)') : 'No discount',
            'current_due' => $currentDue,
            'previous_pending' => $previousPending,
            'previous_months' => $previousMonths ?: 'No previous pending',
            'total_due' => $totalDue,
            'day' => date('j', strtotime($paymentDate)),
            'payment_date' => $paymentDate,
            'month' => $month,
            'year' => $year,
            'resident_name' => $resident->name,
            'resident_code' => $resident->resident_code
        ];
    }

    /**
     * Generate Remark based on payment allocation
     */
    private function generateRemark($resident, $month, $year, $paymentDate, $totalPaid, $previousPaid, $currentPaid, $previousBalance, $currentBalance, $totalBalance, $advanceAmount)
    {
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $currentMonthLabel = $monthName . ' ' . $year;
        $previousMonths = $this->getPreviousMonthsList($resident->id, $month, $year);

        $remarkParts = [];

        // 1. Previous pending clearing
        if ($previousPaid > 0) {
            if ($previousPaid >= $this->getPreviousPending($resident->id, $month, $year)) {
                $remarkParts[] = "✅ Previous pending cleared: ₹" . number_format($previousPaid, 2) . " (" . $previousMonths . ")";
            } else {
                $remarkParts[] = "🟡 Partial clearing of previous pending: ₹" . number_format($previousPaid, 2) . " paid. Remaining: ₹" . number_format($previousBalance, 2);
            }
        } else {
            $prevPending = $this->getPreviousPending($resident->id, $month, $year);
            if ($prevPending > 0) {
                $remarkParts[] = "⚠️ Previous months pending: ₹" . number_format($prevPending, 2) . " (" . $previousMonths . "). Not cleared.";
            }
        }

        // 2. Current month payment
        $discount = $this->calculateDiscount($paymentDate);
        if ($currentPaid > 0) {
            if ($currentPaid >= ($resident->rent_amount - $discount)) {
                $remarkParts[] = "✅ {$currentMonthLabel} payment completed: ₹" . number_format($currentPaid, 2);
            } else {
                $remarkParts[] = "🟡 Partial payment for {$currentMonthLabel}: ₹" . number_format($currentPaid, 2) . " paid. Balance: ₹" . number_format($currentBalance, 2);
            }
        } else {
            $remarkParts[] = "⏳ {$currentMonthLabel} not paid";
        }

        // 3. Advance payment (if any)
        if ($advanceAmount > 0) {
            $remarkParts[] = "💰 Advance payment: ₹" . number_format($advanceAmount, 2) . " (will adjust next month)";
        }

        // 4. Total balance
        if ($totalBalance > 0) {
            $remarkParts[] = "📊 Total pending: ₹" . number_format($totalBalance, 2);
        } else {
            $remarkParts[] = "✅ All dues cleared!";
        }

        return implode(" | ", $remarkParts);
    }

    /**
     * Generate detailed remark
     */
    private function generateDetailedRemark($resident, $month, $year, $paymentDate, $totalPaid, $previousPaid, $currentPaid, $advanceAmount, $previousBalance, $currentBalance, $totalBalance, $previousPendingList, $totalPreviousPending)
    {
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $currentMonthLabel = $monthName . ' ' . $year;
        $discount = $this->calculateDiscount($paymentDate);

        $remarkParts = [];

        // 1. Previous Pending Details
        if ($previousPaid > 0) {
            $clearedMonths = [];
            foreach ($previousPendingList as $prev) {
                if ($prev->balance_amount <= 0) {
                    $clearedMonths[] = date('F Y', mktime(0,0,0,$prev->month,1,$prev->year));
                }
            }

            if (!empty($clearedMonths)) {
                $remarkParts[] = "✅ Previous pending cleared: ₹" . number_format($previousPaid, 2) . " (" . implode(', ', $clearedMonths) . ")";
            } else {
                $remarkParts[] = "✅ Previous pending cleared: ₹" . number_format($previousPaid, 2);
            }

            if ($previousBalance > 0) {
                $remainingMonths = [];
                foreach ($previousPendingList as $prev) {
                    if ($prev->balance_amount > 0) {
                        $remainingMonths[] = date('F Y', mktime(0,0,0,$prev->month,1,$prev->year)) . " (₹" . number_format($prev->balance_amount, 2) . ")";
                    }
                }
                $remarkParts[] = "⚠️ Remaining previous pending: ₹" . number_format($previousBalance, 2) . " (" . implode(', ', $remainingMonths) . ")";
            }
        } else {
            if ($totalPreviousPending > 0) {
                $pendingMonths = [];
                foreach ($previousPendingList as $prev) {
                    $pendingMonths[] = date('F Y', mktime(0,0,0,$prev->month,1,$prev->year)) . " (₹" . number_format($prev->balance_amount, 2) . ")";
                }
                $remarkParts[] = "⚠️ Previous months pending: ₹" . number_format($totalPreviousPending, 2) . " (" . implode(', ', $pendingMonths) . "). Not cleared.";
            }
        }

        // 2. Current Month Payment
        if ($currentPaid > 0) {
            if ($currentPaid >= ($resident->rent_amount - $discount)) {
                $remarkParts[] = "✅ {$currentMonthLabel} payment completed: ₹" . number_format($currentPaid, 2);
            } else {
                $remarkParts[] = "🟡 Partial payment for {$currentMonthLabel}: ₹" . number_format($currentPaid, 2) . " paid. Balance: ₹" . number_format($currentBalance, 2);
            }
        } else {
            $remarkParts[] = "⏳ {$currentMonthLabel} not paid";
        }

        // 3. Advance Payment
        if ($advanceAmount > 0) {
            $remarkParts[] = "💰 Advance payment: ₹" . number_format($advanceAmount, 2) . " (will adjust next month)";
        }

        // 4. Total Balance
        if ($totalBalance > 0) {
            $remarkParts[] = "📊 Total pending: ₹" . number_format($totalBalance, 2);
        } else {
            $remarkParts[] = "✅ All dues cleared!";
        }

        return implode(" | ", $remarkParts);
    }

    /**
     * Build detailed response message
     */
    private function buildDetailedResponseMessage($totalPaid, $previousPaid, $currentPaid, $advanceAmount, $previousBalance, $currentBalance, $totalBalance, $totalPreviousPending, $previousClearedCount, $receiptNo, $isExisting = false)
    {
        $messages = [];

        if ($isExisting) {
            $messages[] = "✅ Payment updated successfully!";
        } else {
            $messages[] = "✅ Payment recorded successfully!";
        }

        $messages[] = "📋 Receipt: " . $receiptNo;
        $messages[] = "💰 Total paid: ₹" . number_format($totalPaid, 2);
        $messages[] = "─────────────────────";

        if ($previousPaid > 0) {
            $messages[] = "📅 Previous pending cleared: ₹" . number_format($previousPaid, 2) . " (" . $previousClearedCount . " month(s))";
            if ($previousBalance > 0) {
                $messages[] = "⚠️ Remaining previous pending: ₹" . number_format($previousBalance, 2);
            }
        } else {
            if ($totalPreviousPending > 0) {
                $messages[] = "⚠️ Previous pending: ₹" . number_format($totalPreviousPending, 2) . " (not cleared)";
            } else {
                $messages[] = "✅ No previous pending";
            }
        }

        if ($currentPaid > 0) {
            $messages[] = "📅 Current month paid: ₹" . number_format($currentPaid, 2);
            if ($currentBalance > 0) {
                $messages[] = "⚠️ Current month remaining: ₹" . number_format($currentBalance, 2);
            } else {
                $messages[] = "✅ Current month fully paid!";
            }
        }

        if ($advanceAmount > 0) {
            $messages[] = "💰 Advance payment: ₹" . number_format($advanceAmount, 2) . " (will adjust next month)";
        }

        $messages[] = "─────────────────────";
        if ($totalBalance <= 0) {
            $messages[] = "✅ All dues cleared!";
        } else {
            $messages[] = "⚠️ Total pending: ₹" . number_format($totalBalance, 2);
        }

        return implode("\n", $messages);
    }

    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $user = auth()->user();

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->get();
        }

        // Get all active residents for dropdown
        if ($user->role === 'admin') {
            $residents = Resident::with(['hostel', 'room'])
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $residents = Resident::with(['hostel', 'room'])
                ->whereIn('hostel_id', $hostelIds)
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get();
        }

        // Get filter values from request
        $filterMonth = request()->month ?? now()->month;
        $filterYear = request()->year ?? now()->year;
        $filterHostelId = request()->hostel_id ?? null;
        $filterStatus = request()->status ?? null;

        // BUILD MAIN QUERY WITH FILTERS
        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $filterMonth)
            ->where('year', $filterYear);

        // Apply hostel filter
        if ($filterHostelId) {
            $query->whereHas('resident', function ($q) use ($filterHostelId) {
                $q->where('hostel_id', $filterHostelId);
            });
        }

        // Apply status filter
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        // Apply user role restriction
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // CALCULATE STATISTICS BASED ON FILTERED PAYMENTS ONLY
        $stats = [
            'total' => $payments->count(),
            'pending' => $payments->where('status', 'PENDING')->count(),
            'paid' => $payments->where('status', 'PAID')->count(),
            'partial' => $payments->where('status', 'PARTIAL')->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_discount' => $payments->sum('discount_amount'),
            'total_fine' => $payments->sum('fine_amount'),
            'total_cash' => $payments->sum('cash_paid_amount'),
            'total_upi' => $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount')
        ];

        // Get current month pending payments (filtered)
        $pendingPayments = $payments->where('status', 'PENDING');

        // Get monthly summary (filtered)
        $monthlySummary = Payment::selectRaw('month, year, COUNT(*) as count, SUM(rent_amount) as total_rent, SUM(balance_amount) as total_balance, SUM(cash_paid_amount + upi_paid_amount) as total_collected')
            ->where('month', $filterMonth)
            ->where('year', $filterYear);

        if ($filterHostelId) {
            $monthlySummary->whereHas('resident', function ($q) use ($filterHostelId) {
                $q->where('hostel_id', $filterHostelId);
            });
        }

        if ($filterStatus) {
            $monthlySummary->where('status', $filterStatus);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $monthlySummary->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $monthlySummary = $monthlySummary->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Get hostel-wise summary (filtered)
        $hostelSummaryQuery = Payment::with('resident.hostel')
            ->where('month', $filterMonth)
            ->where('year', $filterYear);

        if ($filterHostelId) {
            $hostelSummaryQuery->whereHas('resident', function ($q) use ($filterHostelId) {
                $q->where('hostel_id', $filterHostelId);
            });
        }

        if ($filterStatus) {
            $hostelSummaryQuery->where('status', $filterStatus);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $hostelSummaryQuery->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $hostelSummary = $hostelSummaryQuery->get()
            ->groupBy('resident.hostel_id')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'hostel_name' => $first->resident->hostel->hostel_name ?? 'N/A',
                    'total_count' => $group->count(),
                    'total_rent' => $group->sum('rent_amount'),
                    'total_collected' => $group->sum('cash_paid_amount') + $group->sum('upi_paid_amount'),
                    'total_balance' => $group->sum('balance_amount'),
                    'paid_count' => $group->where('status', 'PAID')->count(),
                    'pending_count' => $group->where('status', 'PENDING')->count(),
                    'partial_count' => $group->where('status', 'PARTIAL')->count()
                ];
            });

        // Get filter labels for display
        $filterMonthName = date('F', mktime(0, 0, 0, $filterMonth, 1));
        $filterHostelName = $filterHostelId ? Hostel::find($filterHostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        return view('admin.payments.index', compact(
            'payments',
            'hostels',
            'stats',
            'pendingPayments',
            'monthlySummary',
            'residents',
            'hostelSummary',
            'user',
            'filterMonth',
            'filterYear',
            'filterMonthName',
            'filterHostelName',
            'filterHostelId',
            'filterStatus'
        ));
    }

    // ============================================================
    // PAYMENT DETAILS API
    // ============================================================

    /**
     * Get payment details for preview
     */
    public function getPaymentDetails(Request $request)
    {
        try {
            $resident = Resident::find($request->resident_id);
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ]);
            }

            $paymentDate = $request->payment_date;
            $month = $request->month;
            $year = $request->year;

            $details = $this->calculatePaymentDetails($resident, $month, $year, $paymentDate);

            // Calculate what happens with given payment amount
            $totalPaid = $request->total_paid ?? 0;

            $previousPending = $details['previous_pending'];
            $currentDue = $details['current_due'];

            $remaining = $totalPaid;
            $previousPaid = min($remaining, $previousPending);
            $remaining -= $previousPaid;
            $currentPaid = min($remaining, $currentDue);
            $remaining -= $currentPaid;
            $advanceAmount = max(0, $remaining);

            $previousBalance = max(0, $previousPending - $previousPaid);
            $currentBalance = max(0, $currentDue - $currentPaid);
            $totalBalance = $previousBalance + $currentBalance;

            $details['total_paid'] = $totalPaid;
            $details['previous_paid'] = $previousPaid;
            $details['current_paid'] = $currentPaid;
            $details['advance_amount'] = $advanceAmount;
            $details['previous_balance'] = $previousBalance;
            $details['current_balance'] = $currentBalance;
            $details['total_balance'] = $totalBalance;

            // Preview remark
            $details['preview_remark'] = $this->generateRemark(
                $resident,
                $month,
                $year,
                $paymentDate,
                $totalPaid,
                $previousPaid,
                $currentPaid,
                $previousBalance,
                $currentBalance,
                $totalBalance,
                $advanceAmount
            );

            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // STORE METHOD - FIXED WITH DISCOUNT ELIGIBILITY CHECK
    // ============================================================

    public function store(Request $request)
    {
        $user = auth()->user();

        $resident = Resident::find($request->resident_id);
        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Resident not found!'
            ], 404);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to add payments for this resident!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'resident_id' => 'required|exists:residents,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'cash_paid_amount' => 'required|numeric|min:0',
            'upi_paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:500',
            'discount_amount' => 'nullable|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $paymentDate = $request->payment_date;
            $month = $request->month;
            $year = $request->year;

            // Check if already paid for this month
            $existingPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingPayment && $existingPayment->status === 'PAID') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ Payment already completed for " . date('F Y', mktime(0,0,0,$month,1,$year)) . "!\n" .
                                 "Receipt: {$existingPayment->receipt_no}\n" .
                                 "Amount: ₹" . number_format($existingPayment->rent_amount, 2) . "\n" .
                                 "Status: PAID ✅",
                    'data' => [
                        'existing_payment' => $existingPayment,
                        'receipt_no' => $existingPayment->receipt_no,
                        'amount' => $existingPayment->rent_amount,
                        'status' => $existingPayment->status
                    ]
                ], 422);
            }

            // GET PREVIOUS PENDING PAYMENTS (WITH DETAILS)
            $previousPendingList = $this->getPreviousPendingDetails($resident->id, $month, $year);
            $totalPreviousPending = $previousPendingList->sum('balance_amount');

            $totalPaid = $request->cash_paid_amount + $request->upi_paid_amount;
            $fullRent = (float) ($resident->rent_amount ?? 0);

            // ✅ FIX: Calculate discount ONLY IF ELIGIBLE
            // Eligibility: No previous pending AND payment covers full rent
            $tentativeDiscount = (float) $this->calculateDiscount($paymentDate);
            $fine = (float) ($request->fine_amount ?? 0);

            // Check eligibility
            $hasPreviousPending = $totalPreviousPending > 0;
            $amountForCurrentMonth = $totalPaid - $totalPreviousPending;
            $canCoverFullRent = $amountForCurrentMonth >= $fullRent;

            // ✅ Apply discount ONLY if eligible
            if (!$hasPreviousPending && $canCoverFullRent) {
                $discount = $tentativeDiscount;
                $discountReason = '✅ Eligible: No pending & Full payment';
            } else {
                $discount = 0;
                if ($hasPreviousPending) {
                    $discountReason = '❌ No discount: Previous pending exists (₹' . number_format($totalPreviousPending, 2) . ')';
                } elseif (!$canCoverFullRent) {
                    $discountReason = '❌ No discount: Partial payment (₹' . number_format($amountForCurrentMonth, 2) . ' of ₹' . number_format($fullRent, 2) . ')';
                } else {
                    $discountReason = '❌ No discount applied';
                }
            }

            $currentDue = $fullRent - $discount + $fine;

            // AUTOMATIC ALLOCATION: Previous → Current → Advance
            $remaining = $totalPaid;

            // 1. FIRST: Clear Previous Pending (Oldest to Newest)
            $previousPaid = 0;
            $previousClearedCount = 0;

            foreach ($previousPendingList as $prevPayment) {
                if ($remaining <= 0) break;

                $prevBalance = $prevPayment->balance_amount;
                $payAmount = min($remaining, $prevBalance);

                // Update previous payment
                $prevPayment->cash_paid_amount += $payAmount;
                $newBalance = $prevBalance - $payAmount;
                $prevPayment->balance_amount = max(0, $newBalance);
                $prevPayment->status = ($newBalance <= 0) ? 'PAID' : 'PARTIAL';

                // Append transaction ID
                if ($request->transaction_id && $payAmount > 0) {
                    if ($prevPayment->transaction_id) {
                        $prevPayment->transaction_id .= ' / ' . $request->transaction_id;
                    } else {
                        $prevPayment->transaction_id = $request->transaction_id;
                    }
                }

                // Update remark for previous payment
                $prevPayment->remark = ($newBalance <= 0)
                    ? "✅ Cleared on " . date('d M Y', strtotime($paymentDate)) . " (Receipt: " . ($this->receiptNo ?? 'N/A') . ")"
                    : "🟡 Partially cleared: ₹" . number_format($payAmount, 2) . " on " . date('d M Y', strtotime($paymentDate)) . ". Remaining: ₹" . number_format($newBalance, 2);

                $prevPayment->save();

                $previousPaid += $payAmount;
                $remaining -= $payAmount;
                $previousClearedCount++;
            }

            $previousBalance = max(0, $totalPreviousPending - $previousPaid);

            // 2. SECOND: Pay Current Month
            $currentPaid = min($remaining, $currentDue);
            $remaining -= $currentPaid;
            $currentBalance = max(0, $currentDue - $currentPaid);

            // 3. THIRD: Remaining goes to Advance Payment
            $advanceAmount = max(0, $remaining);

            $totalBalance = $previousBalance + $currentBalance;

            // Determine status
            $status = 'PENDING';
            if ($totalBalance <= 0) {
                $status = 'PAID';
            } elseif ($totalPaid > 0) {
                $status = 'PARTIAL';
            }

            // Generate receipt
            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
            $this->receiptNo = $receiptNo;

            // CREATE PAYMENT FOR CURRENT MONTH (or Update if exists)
            if ($existingPayment) {
                // Update existing payment (for partial completion)
                $existingPayment->cash_paid_amount += $currentPaid;
                $existingPayment->upi_paid_amount += 0;
                $existingPayment->balance_amount = $currentBalance;
                $existingPayment->status = $status;
                $existingPayment->payment_date = $paymentDate;
                $existingPayment->transaction_id = $request->transaction_id;
                $existingPayment->discount_amount = $discount;
                $existingPayment->save();

                $payment = $existingPayment;
            } else {
                // Create new payment
                $payment = Payment::create([
                    'resident_id' => $resident->id,
                    'receipt_no' => $receiptNo,
                    'month' => $month,
                    'year' => $year,
                    'rent_amount' => $fullRent,
                    'discount_amount' => $discount,
                    'fine_amount' => $fine,
                    'cash_paid_amount' => $currentPaid,
                    'upi_paid_amount' => 0,
                    'balance_amount' => $currentBalance,
                    'payment_date' => $paymentDate,
                    'transaction_id' => $request->transaction_id,
                    'status' => $status,
                    'payment_type' => 'all',
                    'previous_pending_cleared' => $previousPaid,
                ]);
            }

            // GENERATE COMPREHENSIVE REMARK
            $remark = $this->generateDetailedRemark(
                $resident,
                $month,
                $year,
                $paymentDate,
                $totalPaid,
                $previousPaid,
                $currentPaid,
                $advanceAmount,
                $previousBalance,
                $currentBalance,
                $totalBalance,
                $previousPendingList,
                $totalPreviousPending
            );

            // Add discount eligibility info to remark
            if ($discount > 0) {
                $remark = "✅ Discount applied: ₹" . number_format($discount, 2) . " (Eligible: No pending, Full payment) | " . $remark;
            } else {
                $remark = $discountReason . " | " . $remark;
            }

            $payment->remark = $remark;
            $payment->save();

            $payment->load(['resident.hostel', 'resident.room']);

            DB::commit();

            // BUILD RESPONSE MESSAGE
            $message = $this->buildDetailedResponseMessage(
                $totalPaid,
                $previousPaid,
                $currentPaid,
                $advanceAmount,
                $previousBalance,
                $currentBalance,
                $totalBalance,
                $totalPreviousPending,
                $previousClearedCount,
                $receiptNo,
                $existingPayment ? true : false
            );

            // Add discount info to response
            $discountMessage = $discount > 0
                ? "✅ Discount applied: ₹" . number_format($discount, 2)
                : $discountReason;

            return response()->json([
                'success' => true,
                'message' => $message . "\n" . $discountMessage,
                'data' => [
                    'payment' => $payment,
                    'receipt_no' => $receiptNo,
                    'total_paid' => $totalPaid,
                    'previous_pending_cleared' => $previousPaid,
                    'previous_pending_remaining' => $previousBalance,
                    'current_month_paid' => $currentPaid,
                    'current_month_balance' => $currentBalance,
                    'advance_payment' => $advanceAmount,
                    'total_balance' => $totalBalance,
                    'status' => $status,
                    'discount_applied' => $discount,
                    'discount_eligible' => $discount > 0,
                    'discount_type' => $discount > 0 ? ($discount == 250 ? 'Early Bird (1st-5th)' : 'Early Payment (6th-10th)') : 'No discount',
                    'discount_reason' => $discount > 0 ? 'No pending & Full payment' : ($totalPreviousPending > 0 ? 'Previous pending exists' : 'Partial payment'),
                    'fine_applied' => $fine,
                    'previous_months_cleared' => $previousClearedCount,
                    'is_existing_payment' => $existingPayment ? true : false,
                    'remark' => $remark,
                    'calculation' => [
                        'rent' => $fullRent,
                        'discount' => $discount,
                        'previous_pending' => $totalPreviousPending,
                        'current_due' => $currentDue,
                        'total_due' => $currentDue + $totalPreviousPending,
                        'total_paid' => $totalPaid,
                        'payment_day' => date('j', strtotime($paymentDate))
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // EXISTING METHODS
    // ============================================================

    public function getResidentsByRoom($roomId)
    {
        $user = auth()->user();

        $query = Resident::where('room_id', $roomId)
            ->where('status', 'ACTIVE')
            ->orderBy('name');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }

        $residents = $query->get(['id', 'name', 'resident_code', 'hostel_id', 'room_id', 'rent_amount']);

        return response()->json([
            'success' => true,
            'data' => $residents
        ]);
    }

    public function getPartialPaymentDetails($residentId, $month, $year)
    {
        $payment = Payment::where('resident_id', $residentId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'PARTIAL')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'No partial payment found for this period'
            ]);
        }

        $totalPaid = ($payment->cash_paid_amount ?? 0) +
                     ($payment->upi_paid_amount ?? 0);

        $totalAmount = $payment->rent_amount - ($payment->discount_amount ?? 0) + ($payment->fine_amount ?? 0);
        $remainingBalance = max(0, $totalAmount - $totalPaid);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'receipt_no' => $payment->receipt_no,
                'rent_amount' => $payment->rent_amount,
                'discount_amount' => $payment->discount_amount ?? 0,
                'fine_amount' => $payment->fine_amount ?? 0,
                'cash_paid' => $payment->cash_paid_amount ?? 0,
                'upi_paid' => $payment->upi_paid_amount ?? 0,
                'total_paid' => $totalPaid,
                'balance_amount' => $remainingBalance,
                'transaction_id_raw' => $payment->transaction_id,
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => $payment->status,
                'remark' => $payment->remark
            ]
        ]);
    }

    public function edit($id)
    {
        $user = auth()->user();
        $payment = Payment::with(['resident', 'resident.hostel', 'resident.room'])->findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit this payment!'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this payment!'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'rent_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
            'cash_paid_amount' => 'required|numeric|min:0',
            'upi_paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:255',
            'status' => 'required|in:PAID,PARTIAL,PENDING'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $totalPaid = $request->cash_paid_amount + $request->upi_paid_amount;
        $totalAmount = $request->rent_amount - ($request->discount_amount ?? 0) + ($request->fine_amount ?? 0);
        $balanceAmount = max(0, $totalAmount - $totalPaid);

        $payment->update([
            'month' => $request->month,
            'year' => $request->year,
            'discount_amount' => $request->discount_amount ?? 0,
            'fine_amount' => $request->fine_amount ?? 0,
            'cash_paid_amount' => $request->cash_paid_amount,
            'upi_paid_amount' => $request->upi_paid_amount,
            'balance_amount' => $balanceAmount,
            'payment_date' => $request->payment_date,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status
        ]);

        $payment->load(['resident.hostel', 'resident.room']);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully!',
            'data' => $payment
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this payment!'
                ], 403);
            }
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully!'
        ]);
    }

    public function getResidentRent($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        return response()->json([
            'success' => true,
            'data' => [
                'rent_amount' => $resident->rent_amount ?? 0,
                'name' => $resident->name,
                'resident_code' => $resident->resident_code
            ]
        ]);
    }

    public function checkPreviousPending($residentId, $month, $year)
    {
        $hasPendingPrevious = Payment::where('resident_id', $residentId)
            ->where(function ($q) use ($month, $year) {
                $q->where('year', '<', $year)
                    ->orWhere(function ($q2) use ($month, $year) {
                        $q2->where('year', $year)
                            ->where('month', '<', $month);
                    });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->exists();

        return response()->json([
            'success' => true,
            'has_pending' => $hasPendingPrevious
        ]);
    }

    public function getResidentPayments($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        $payments = $resident->payments()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $summary = [
            'total_paid' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'pending_count' => $payments->where('status', 'PENDING')->count(),
            'partial_count' => $payments->where('status', 'PARTIAL')->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $payments,
            'summary' => $summary
        ]);
    }

    public function getResidentDue($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        $pendingPayments = $resident->payments()
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalDue = $pendingPayments->sum('balance_amount');
        $pendingCount = $pendingPayments->count();

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $residentId,
                'resident_name' => $resident->name,
                'total_due' => $totalDue,
                'pending_count' => $pendingCount,
                'payments' => $pendingPayments
            ]
        ]);
    }

    public function markAsPaid($id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this payment!'
                ], 403);
            }
        }

        $totalAmount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;

        $payment->update([
            'cash_paid_amount' => $totalAmount - $payment->upi_paid_amount,
            'balance_amount' => 0,
            'status' => 'PAID'
        ]);

        $payment->load(['resident.hostel', 'resident.room']);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid successfully!',
            'data' => $payment
        ]);
    }

    public function bulkPayment(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'resident_ids' => 'required|array',
            'resident_ids.*' => 'exists:residents,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'payment_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $created = [];
        $errors = [];

        foreach ($request->resident_ids as $residentId) {
            $resident = Resident::find($residentId);

            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($resident->hostel_id, $hostelIds)) {
                    $errors[] = "No permission for resident: {$resident->name}";
                    continue;
                }
            }

            $exists = Payment::where('resident_id', $residentId)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();

            if ($exists) {
                $errors[] = "Payment already exists for " . $resident->name;
                continue;
            }

            $previousPending = $this->getPreviousPending($residentId, $request->month, $request->year);
            if ($previousPending > 0) {
                $errors[] = "Previous months pending for " . $resident->name . " (₹" . number_format($previousPending, 2) . ")";
                continue;
            }

            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            $discount = $this->calculateDiscount($request->payment_date);
            $currentDue = ($resident->rent_amount ?? 0) - $discount;

            $payment = Payment::create([
                'resident_id' => $residentId,
                'receipt_no' => $receiptNo,
                'month' => $request->month,
                'year' => $request->year,
                'rent_amount' => $resident->rent_amount ?? 0,
                'discount_amount' => $discount,
                'fine_amount' => 0,
                'cash_paid_amount' => 0,
                'upi_paid_amount' => 0,
                'balance_amount' => $currentDue,
                'payment_date' => $request->payment_date,
                'status' => 'PENDING',
                'payment_type' => 'all',
                'remark' => "📅 Pending payment for " . date('F', mktime(0,0,0,$request->month,1)) . " " . $request->year
            ]);

            $created[] = $payment;
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' payments created. ' . count($errors) . ' errors.',
            'data' => $created,
            'errors' => $errors
        ]);
    }

    public function getMonthlySummary()
    {
        $summary = Payment::selectRaw('
            month,
            year,
            COUNT(*) as total_count,
            SUM(rent_amount) as total_rent,
            SUM(discount_amount) as total_discount,
            SUM(fine_amount) as total_fine,
            SUM(cash_paid_amount) as total_cash,
            SUM(upi_paid_amount) as total_upi,
            SUM(balance_amount) as total_balance,
            SUM(cash_paid_amount + upi_paid_amount) as total_collected
        ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id',
            'status' => 'required|in:PAID,PARTIAL,PENDING'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payments = Payment::whereIn('id', $request->ids)->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to update some payments!'
                    ], 403);
                }
            }
        }

        $updated = 0;
        foreach ($payments as $payment) {
            if ($request->status === 'PAID') {
                $totalAmount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;
                $payment->cash_paid_amount = $totalAmount - $payment->upi_paid_amount;
                $payment->balance_amount = 0;
            } elseif ($request->status === 'PENDING') {
                $payment->cash_paid_amount = 0;
                $payment->upi_paid_amount = 0;
                $payment->balance_amount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;
            }
            $payment->status = $request->status;
            $payment->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => $updated . ' payments updated to ' . $request->status
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payments = Payment::whereIn('id', $request->ids)->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to delete some payments!'
                    ], 403);
                }
            }
        }

        $deleted = Payment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' payments deleted successfully!'
        ]);
    }

    // ============================================================
    // HELPER METHODS FOR EXPORT
    // ============================================================

    private function filterResidentsByMonth($query, $month, $year)
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));

        return $query->where(function($q) use ($startDate, $endDate) {
            $q->where('joining_date', '<=', $endDate)
              ->where(function($sub) use ($startDate) {
                  $sub->whereNull('vacate_date')
                      ->orWhere('vacate_date', '>=', $startDate);
              });
        });
    }

    private function csvNumber($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function csvString($value): string
    {
        if (is_null($value)) return '';
        $value = str_replace(',', ';', $value);
        $value = str_replace('"', '', $value);
        return $value;
    }

    private function applyExportFilters($query, $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }
        return $query;
    }

    private function getRoomDetails($resident)
    {
        if (!$resident->room) {
            return ['room_no' => 'N/A', 'bed_no' => 'N/A'];
        }
        return [
            'room_no' => $resident->room->room_no ?? 'N/A',
            'bed_no' => $resident->bed_no ?? 'N/A'
        ];
    }

   private function getUnpaidResidentsWithDetails($resident, $month, $year)
{
    // Get all payments for this resident
    $allPayments = Payment::where('resident_id', $resident->id)
        ->orderBy('year', 'asc')->orderBy('month', 'asc')->get();

    // Separate previous and current payments
    $previousPayments = $allPayments->filter(function($p) use ($month, $year) {
        return $p->year < $year || ($p->year == $year && $p->month < $month);
    });

    $currentPayment = $allPayments->filter(function($p) use ($month, $year) {
        return $p->month == $month && $p->year == $year;
    })->first();

    // Calculate pending amounts
    $totalPreviousPending = $previousPayments->sum('balance_amount');
    $hasPreviousPending = $totalPreviousPending > 0;
    $isCurrentPaid = $currentPayment && $currentPayment->status == 'PAID';

    // Today's discount based on current date
    $todayDiscount = (float) $this->calculateDiscount(now()->toDateString());
    $rentAmount = (float) ($resident->rent_amount ?? 0);

    // ✅ DISCOUNT LOGIC:
    // Rule 1: If NO previous pending → Apply TODAY's discount
    // Rule 2: If HAS previous pending → NO discount
    if (!$hasPreviousPending) {
        // ✅ Eligible for discount
        $discountApplied = $todayDiscount;
        
        if ($currentPayment) {
            // Has payment record - reduce balance by today's discount
            $balance = (float) $currentPayment->balance_amount;
            $effectiveCurrentBalance = max(0, $balance - $todayDiscount);
        } else {
            // No payment record - show discounted rent
            $effectiveCurrentBalance = $rentAmount - $todayDiscount;
        }
    } else {
        // ❌ NOT eligible for discount
        $discountApplied = 0;
        
        if ($currentPayment) {
            $effectiveCurrentBalance = (float) $currentPayment->balance_amount;
        } else {
            $effectiveCurrentBalance = $rentAmount;
        }
    }

    // Total due = previous pending + current balance (with/without discount)
    $totalDue = (float) $totalPreviousPending + (float) $effectiveCurrentBalance;

    // Determine status
    $overallStatus = 'NO PAYMENT';
    if ($totalPreviousPending > 0 && $effectiveCurrentBalance > 0) {
        $overallStatus = 'PENDING (Previous + Current)';
    } elseif ($totalPreviousPending > 0 && $effectiveCurrentBalance == 0) {
        $overallStatus = 'PENDING (Previous Only)';
    } elseif ($totalPreviousPending == 0 && $effectiveCurrentBalance > 0) {
        $overallStatus = 'PENDING (Current Only)';
    } elseif ($totalPreviousPending == 0 && $effectiveCurrentBalance == 0 && $isCurrentPaid) {
        $overallStatus = 'PAID';
    } elseif ($currentPayment && $currentPayment->status == 'PARTIAL') {
        $overallStatus = 'PARTIAL';
    }

    // Previous months details for export
    $previousMonthsDetails = [];
    foreach ($previousPayments as $prevPayment) {
        if ($prevPayment->balance_amount > 0) {
            $previousMonthsDetails[] = [
                'month' => $prevPayment->month,
                'year' => $prevPayment->year,
                'month_name' => date('F', mktime(0,0,0,$prevPayment->month,1)),
                'rent' => (float) $prevPayment->rent_amount,
                'discount' => (float) ($prevPayment->discount_amount ?? 0),
                'paid' => (float) ($prevPayment->cash_paid_amount + $prevPayment->upi_paid_amount),
                'balance' => (float) $prevPayment->balance_amount,
                'status' => $prevPayment->status,
                'remark' => $prevPayment->remark ?? ''
            ];
        }
    }

    return [
        'resident' => $resident,
        'current_payment' => $currentPayment,
        'previous_payments' => $previousPayments,
        'previous_months_details' => $previousMonthsDetails,
        'total_previous_pending' => (float) $totalPreviousPending,
        'previous_pending_count' => $previousPayments->whereIn('status', ['PENDING', 'PARTIAL'])->count(),
        'current_balance' => (float) $effectiveCurrentBalance,
        'current_month_rent' => (float) $effectiveCurrentBalance, // ✅ FIXED: Use same value
        'current_status' => $currentPayment ? $currentPayment->status : 'NO PAYMENT',
        'is_current_paid' => $isCurrentPaid,
        'total_due' => (float) $totalDue,
        'overall_status' => $overallStatus,
        'total_rent' => $rentAmount,
        'discount_applied' => $discountApplied,
        'discount_eligible' => $discountApplied > 0,
        'discount_type' => $discountApplied > 0 ? ($discountApplied == 250 ? 'Early Bird (1st-5th)' : 'Early Payment (6th-10th)') : 'No discount',
        'today_date' => now()->format('d M Y'),
        'day_of_month' => now()->day,
        'remark' => $currentPayment ? ($currentPayment->remark ?? '') : 'No payment recorded',
        'breakdown' => [
            'previous_months' => $previousMonthsDetails,
            'total_previous_pending' => (float) $totalPreviousPending,
            'current_month_due_original' => $rentAmount,
            'current_month_discount' => $discountApplied,
            'current_month_due_after_discount' => (float) $effectiveCurrentBalance,
            'total_due' => (float) $totalDue
        ]
    ];
}
    // ============================================================
    // EXPORT METHODS
    // ============================================================

    public function exportUnpaidWithDetails(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

        try {
            $residentsQuery = Resident::with(['hostel', 'room'])
                ->where('status', 'ACTIVE');

            $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!empty($hostelIds)) {
                    $residentsQuery->whereIn('hostel_id', $hostelIds);
                }
            }

            if ($hostelId) {
                $residentsQuery->where('hostel_id', $hostelId);
            }

            $residents = $residentsQuery->orderBy('name')->get();

            $unpaidResidents = [];
            $totalPreviousPending = 0;
            $totalCurrentBalance = 0;
            $totalDue = 0;
            $totalUnpaid = 0;
            $totalPaid = 0;
            $totalDiscount = 0;

            foreach ($residents as $resident) {
                $details = $this->getUnpaidResidentsWithDetails($resident, $month, $year);

                $totalDueAmount = isset($details['total_due']) ? (float) $details['total_due'] : 0;
                $overallStatus = isset($details['overall_status']) ? $details['overall_status'] : 'PAID';

                if ($totalDueAmount > 0 || $overallStatus != 'PAID') {
                    $unpaidResidents[] = $details;
                    $totalPreviousPending += isset($details['total_previous_pending']) ? (float) $details['total_previous_pending'] : 0;
                    $totalCurrentBalance += isset($details['current_balance']) ? (float) $details['current_balance'] : 0;
                    $totalDue += $totalDueAmount;
                    $totalDiscount += isset($details['discount_applied']) ? (float) $details['discount_applied'] : 0;
                    $totalUnpaid++;

                    if (isset($details['current_payment']) && $details['current_payment']) {
                        $totalPaid += (float) ($details['current_payment']->cash_paid_amount ?? 0) + (float) ($details['current_payment']->upi_paid_amount ?? 0);
                    }
                }
            }

            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $hostelName = $hostelId ? (Hostel::find($hostelId)->hostel_name ?? 'All Hostels') : 'All Hostels';
            $today = now()->format('d M Y');
            $todayDiscount = $this->calculateDiscount(now()->toDateString());

            // ✅ Build CSV
            $csv = "==================================================\n";
            $csv .= "UNPAID PAYMENTS REPORT\n";
            $csv .= "==================================================\n";
            $csv .= "Report Month: {$monthName} {$year}\n";
            $csv .= "Report Generated: {$today}\n";
            $csv .= "Today's Date: {$today}\n";
            $csv .= "Today's Discount Rate: ₹" . number_format((float) $todayDiscount, 2) . "\n";
            $csv .= "Hostel: {$hostelName}\n";
            $csv .= "Total Unpaid Residents: {$totalUnpaid}\n";
            $csv .= "Total Due: ₹" . number_format((float) $totalDue, 2) . "\n";
            $csv .= "==================================================\n\n";

            // ✅ SUMMARY TABLE
            $csv .= "--- SUMMARY ---\n";
            $csv .= "Total Unpaid Residents,{$totalUnpaid}\n";
            $csv .= "Total Previous Pending (All Months),₹" . number_format((float) $totalPreviousPending, 2) . "\n";
            $csv .= "Total Current Month Balance,₹" . number_format((float) $totalCurrentBalance, 2) . "\n";
            $csv .= "Total Discount Applied,₹" . number_format((float) $totalDiscount, 2) . "\n";
            $csv .= "Total Due,₹" . number_format((float) $totalDue, 2) . "\n\n";

            // ✅ DETAILED REPORT
            $csv .= "--- RESIDENT-WISE DETAILS ---\n";
            $csv .= "S.No,Resident,Hostel,Room,Rent (₹),Discount Applied (₹),Current Due (₹),Previous Pending (₹),Total Due (₹),Status,Discount Eligible,Pending Months Count,Remark\n";

            $serialNo = 1;
            foreach ($unpaidResidents as $item) {
                $resident = $item['resident'];
                $roomNo = $resident->room ? $resident->room->room_no : 'N/A';

                $csv .= $serialNo . ",";
                $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                $csv .= $this->csvString($resident->hostel->hostel_name ?? 'N/A') . ",";
                $csv .= $roomNo . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= $this->csvNumber($item['discount_applied'] ?? 0) . ",";
                $csv .= $this->csvNumber($item['current_balance'] ?? 0) . ",";
                $csv .= $this->csvNumber($item['total_previous_pending'] ?? 0) . ",";
                $csv .= $this->csvNumber($item['total_due'] ?? 0) . ",";
                $csv .= ($item['overall_status'] ?? 'UNKNOWN') . ",";
                $csv .= ($item['discount_eligible'] ? 'YES' : 'NO') . ",";
                $csv .= ($item['previous_pending_count'] ?? 0) . ",";
                $csv .= $this->csvString($item['remark'] ?? '') . "\n";
                $serialNo++;
            }

            // ✅ Previous Months Details
            $csv .= "\n\n--- PREVIOUS MONTHS PENDING DETAILS ---\n";
            $csv .= "Resident,Month,Year,Rent (₹),Discount Applied (₹),Paid (₹),Balance (₹),Status,Remark\n";

            foreach ($unpaidResidents as $item) {
                $resident = $item['resident'];
                if (isset($item['previous_months_details']) && is_array($item['previous_months_details'])) {
                    foreach ($item['previous_months_details'] as $prev) {
                        if (isset($prev['balance']) && $prev['balance'] > 0) {
                            $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                            $csv .= ($prev['month_name'] ?? '') . ",";
                            $csv .= ($prev['year'] ?? '') . ",";
                            $csv .= $this->csvNumber($prev['rent'] ?? 0) . ",";
                            $csv .= $this->csvNumber($prev['discount'] ?? 0) . ",";
                            $csv .= $this->csvNumber($prev['paid'] ?? 0) . ",";
                            $csv .= $this->csvNumber($prev['balance'] ?? 0) . ",";
                            $csv .= ($prev['status'] ?? '') . ",";
                            $csv .= $this->csvString($prev['remark'] ?? '') . "\n";
                        }
                    }
                }
            }

            // ✅ Current Month Details
            $csv .= "\n\n--- CURRENT MONTH DETAILS ---\n";
            $csv .= "Resident,Original Rent (₹),Discount Applied (₹),Due After Discount (₹),Payment Status,Discount Eligible,Remark\n";

            foreach ($unpaidResidents as $item) {
                $resident = $item['resident'];
                $csv .= $this->csvString($resident->name ?? 'N/A') . ",";
                $csv .= $this->csvNumber($resident->rent_amount ?? 0) . ",";
                $csv .= $this->csvNumber($item['discount_applied'] ?? 0) . ",";
                $csv .= $this->csvNumber($item['current_month_rent'] ?? 0) . ",";
                $csv .= ($item['current_status'] ?? '') . ",";
                $csv .= ($item['discount_eligible'] ? 'YES' : 'NO') . ",";
                $csv .= $this->csvString($item['remark'] ?? '') . "\n";
            }

            $filename = 'unpaid-payments-' . date('Y-m-d') . '.csv';
            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Export Unpaid Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error exporting unpaid payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Unpaid Payments as HTML Image for WhatsApp
     */
    public function unpaidPaymentsImage(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();

        $unpaidResidents = [];
        $totalPreviousPending = 0;
        $totalCurrentBalance = 0;
        $totalDue = 0;
        $totalUnpaid = 0;

        foreach ($residents as $resident) {
            $details = $this->getUnpaidResidentsWithDetails($resident, $month, $year);
            if ($details['total_due'] > 0 || $details['overall_status'] != 'PAID') {
                $unpaidResidents[] = $details;
                $totalPreviousPending += $details['total_previous_pending'];
                $totalCurrentBalance += $details['current_balance'];
                $totalDue += $details['total_due'];
                $totalUnpaid++;
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Unpaid Payments with Previous Pending',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'unpaidResidents' => $unpaidResidents,
            'totalUnpaid' => $totalUnpaid,
            'totalDue' => $totalDue,
            'totalPreviousPending' => $totalPreviousPending,
            'totalCurrentBalance' => $totalCurrentBalance,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        return view('admin.payments.images.unpaid-payments', $data);
    }

    /**
     * Export Unpaid Payments as PDF with Details
     */
    public function pdfUnpaidWithDetails(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();

        $unpaidResidents = [];
        $totalPreviousPending = 0;
        $totalCurrentBalance = 0;
        $totalDue = 0;
        $totalUnpaid = 0;

        foreach ($residents as $resident) {
            $details = $this->getUnpaidResidentsWithDetails($resident, $month, $year);
            if ($details['total_due'] > 0 || $details['overall_status'] != 'PAID') {
                $unpaidResidents[] = $details;
                $totalPreviousPending += $details['total_previous_pending'];
                $totalCurrentBalance += $details['current_balance'];
                $totalDue += $details['total_due'];
                $totalUnpaid++;
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Unpaid Payments with Previous Pending',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'unpaidResidents' => $unpaidResidents,
            'totalUnpaid' => $totalUnpaid,
            'totalDue' => $totalDue,
            'totalPreviousPending' => $totalPreviousPending,
            'totalCurrentBalance' => $totalCurrentBalance,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.unpaid-with-details', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('unpaid-with-details-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Get Unpaid Residents Summary (AJAX)
     */
    public function getUnpaidSummary(Request $request)
    {
        try {
            $month = $request->filled('month') ? $request->month : date('n');
            $year = $request->filled('year') ? $request->year : date('Y');
            $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

            $residentsQuery = Resident::with(['hostel', 'room'])
                ->where('status', 'ACTIVE');

            $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

            if (auth()->user()->role !== 'admin') {
                $hostelIds = auth()->user()->hostel_ids ?? [];
                $residentsQuery->whereIn('hostel_id', $hostelIds);
            }

            if ($hostelId) {
                $residentsQuery->where('hostel_id', $hostelId);
            }

            $residents = $residentsQuery->orderBy('name')->get();

            $unpaidList = [];
            $totalPreviousPending = 0;
            $totalCurrentBalance = 0;
            $totalDue = 0;
            $totalUnpaid = 0;

            foreach ($residents as $resident) {
                $details = $this->getUnpaidResidentsWithDetails($resident, $month, $year);
                if ($details['total_due'] > 0 || $details['overall_status'] != 'PAID') {
                    $unpaidList[] = [
                        'resident_id' => $resident->id,
                        'name' => $resident->name,
                        'hostel' => $resident->hostel->hostel_name ?? 'N/A',
                        'room' => $resident->room->room_no ?? 'N/A',
                        'phone' => $resident->phone ?? '',
                        'previous_pending' => $details['total_previous_pending'],
                        'current_balance' => $details['current_balance'],
                        'total_due' => $details['total_due'],
                        'status' => $details['overall_status'],
                        'previous_count' => $details['previous_pending_count'],
                        'discount_applied' => $details['discount_applied'],
                        'discount_eligible' => $details['discount_eligible'],
                        'remark' => $details['remark']
                    ];
                    $totalPreviousPending += $details['total_previous_pending'];
                    $totalCurrentBalance += $details['current_balance'];
                    $totalDue += $details['total_due'];
                    $totalUnpaid++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_unpaid' => $totalUnpaid,
                        'total_previous_pending' => $totalPreviousPending,
                        'total_current_balance' => $totalCurrentBalance,
                        'total_due' => $totalDue
                    ],
                    'residents' => $unpaidList,
                    'month' => date('F', mktime(0,0,0,$month,1)) . ' ' . $year,
                    'today_discount' => $this->calculateDiscount(now()->toDateString())
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // PDF EXPORT METHODS (Remaining methods)
    // ============================================================

    public function pdfResidentPaymentStatus(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Resident Payment Status Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'residents' => $residents,
            'payments' => $payments,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.resident-status', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('resident-payment-status-' . date('Y-m-d') . '.pdf');
    }

    public function pdfPendingResidents(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('name')->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $pendingResidents = [];
        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            if (!$payment || $payment->status !== 'PAID') {
                $pendingResidents[] = [
                    'resident' => $resident,
                    'payment' => $payment,
                    'due_amount' => $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0),
                    'status' => $payment ? $payment->status : 'NO PAYMENT',
                    'remark' => $payment ? $payment->remark : 'No payment recorded'
                ];
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Pending Residents Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'pendingResidents' => $pendingResidents,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.pending-residents', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('pending-residents-' . date('Y-m-d') . '.pdf');
    }

    public function pdfAllPayments(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $this->applyExportFilters($query, $request);
        $payments = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'paid' => $payments->where('status', 'PAID')->count(),
            'pending' => $payments->where('status', 'PENDING')->count(),
            'partial' => $payments->where('status', 'PARTIAL')->count()
        ];

        $data = [
            'title' => 'All Payments Report',
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user,
            'filters' => $request->all()
        ];

        $pdf = PDF::loadView('admin.payments.pdf.all-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('all-payments-' . date('Y-m-d') . '.pdf');
    }

    public function pdfPaidPayments(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('status', 'PAID');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $this->applyExportFilters($query, $request);
        $payments = $query->orderBy('payment_date', 'desc')->get();

        $summary = [
            'total' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount')
        ];

        $data = [
            'title' => 'Paid Payments Report',
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.paid-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('paid-payments-' . date('Y-m-d') . '.pdf');
    }

    public function pdfUnpaidPayments(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $unpaidResidents = [];
        $totalDue = 0;
        $totalUnpaid = 0;

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            if (!$payment || $payment->status !== 'PAID') {
                $unpaidResidents[] = [
                    'resident' => $resident,
                    'payment' => $payment,
                    'due_amount' => $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0),
                    'status' => $payment ? $payment->status : 'NO PAYMENT',
                    'remark' => $payment ? $payment->remark : 'No payment recorded'
                ];
                $totalDue += $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0);
                $totalUnpaid++;
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Unpaid Payments Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'unpaidResidents' => $unpaidResidents,
            'totalUnpaid' => $totalUnpaid,
            'totalDue' => $totalDue,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.unpaid-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('unpaid-payments-' . date('Y-m-d') . '.pdf');
    }

    public function pdfHostelWise(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($request->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this hostel\'s data!'
                ], 403);
            }
        }

        $hostel = Hostel::find($request->hostel_id);

        $residents = Resident::with(['room'])
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $query = Payment::with(['resident', 'resident.room'])
            ->whereHas('resident', function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });

        $this->applyExportFilters($query, $request);
        $payments = $query->get();
        $groupedPayments = $payments->groupBy('resident_id');

        $residentSummaries = [];
        $summary = [
            'total_residents' => $residents->count(),
            'total_payments' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
            'total_balance' => $payments->sum('balance_amount'),
            'paid' => $payments->where('status', 'PAID')->count(),
            'pending' => $payments->where('status', 'PENDING')->count(),
            'partial' => $payments->where('status', 'PARTIAL')->count()
        ];

        foreach ($residents as $resident) {
            $residentPayments = $groupedPayments->get($resident->id) ?? collect();
            $residentSummaries[] = [
                'resident' => $resident,
                'payments' => $residentPayments,
                'total_paid' => $residentPayments->sum('cash_paid_amount') + $residentPayments->sum('upi_paid_amount'),
                'total_balance' => $residentPayments->sum('balance_amount'),
                'count' => $residentPayments->count(),
                'status' => $residentPayments->where('status', 'PENDING')->count() > 0 ? 'PENDING' : ($residentPayments->where('status', 'PARTIAL')->count() > 0 ? 'PARTIAL' : ($residentPayments->where('status', 'PAID')->count() > 0 ? 'PAID' : 'NO PAYMENT')),
                'remark' => $residentPayments->first() ? $residentPayments->first()->remark : 'No payment recorded'
            ];
        }

        $data = [
            'title' => 'Hostel Payment Report',
            'hostel' => $hostel,
            'residentSummaries' => $residentSummaries,
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.hostel-wise', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('hostel-' . $hostel->hostel_code . '-report-' . date('Y-m-d') . '.pdf');
    }

    public function pdfPaymentSummary(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
        }

        $hostelSummaries = [];
        $grandTotal = [
            'residents' => 0,
            'payments' => 0,
            'rent' => 0,
            'collected' => 0,
            'balance' => 0,
            'paid' => 0,
            'pending' => 0,
            'partial' => 0
        ];

        foreach ($hostels as $hostel) {
            $residents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->count();

            $payments = Payment::whereHas('resident', function ($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->get();

            $totalRent = $payments->sum('rent_amount');
            $totalCollected = $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount');
            $totalBalance = $payments->sum('balance_amount');
            $paidCount = $payments->where('status', 'PAID')->count();
            $pendingCount = $payments->where('status', 'PENDING')->count();
            $partialCount = $payments->where('status', 'PARTIAL')->count();

            $hostelSummaries[] = [
                'hostel' => $hostel,
                'residents' => $residents,
                'payments_count' => $payments->count(),
                'total_rent' => $totalRent,
                'total_collected' => $totalCollected,
                'total_balance' => $totalBalance,
                'paid' => $paidCount,
                'pending' => $pendingCount,
                'partial' => $partialCount
            ];

            $grandTotal['residents'] += $residents;
            $grandTotal['payments'] += $payments->count();
            $grandTotal['rent'] += $totalRent;
            $grandTotal['collected'] += $totalCollected;
            $grandTotal['balance'] += $totalBalance;
            $grandTotal['paid'] += $paidCount;
            $grandTotal['pending'] += $pendingCount;
            $grandTotal['partial'] += $partialCount;
        }

        $data = [
            'title' => 'Payment Summary Report',
            'hostelSummaries' => $hostelSummaries,
            'grandTotal' => $grandTotal,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.payment-summary', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payment-summary-' . date('Y-m-d') . '.pdf');
    }

    public function pdfMonthlyUnpaid(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
            'hostel_id' => 'nullable|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $month = $request->month;
        $year = $request->year;
        $hostelId = $request->hostel_id;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $allResidents = $residentsQuery->get();
        $payments = Payment::where('month', $month)->where('year', $year)->get()->keyBy('resident_id');

        $unpaidResidents = [];
        $totalUnpaid = 0;
        $totalDue = 0;

        foreach ($allResidents as $resident) {
            $payment = $payments->get($resident->id);
            if (!$payment || $payment->status !== 'PAID') {
                $unpaidResidents[] = [
                    'resident' => $resident,
                    'payment' => $payment,
                    'due_amount' => $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0),
                    'status' => $payment ? $payment->status : 'NO PAYMENT',
                    'remark' => $payment ? $payment->remark : 'No payment recorded'
                ];
                $totalDue += $payment ? $payment->balance_amount : ($resident->rent_amount ?? 0);
                $totalUnpaid++;
            }
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $hostelName = $hostelId ? Hostel::find($hostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        $data = [
            'title' => 'Monthly Unpaid Payment Report',
            'month' => $monthName,
            'year' => $year,
            'hostel' => $hostelName,
            'unpaidResidents' => $unpaidResidents,
            'totalUnpaid' => $totalUnpaid,
            'totalDue' => $totalDue,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.monthly-unpaid', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('monthly-unpaid-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function pdfReceipt($id)
    {
        $user = auth()->user();
        $payment = Payment::with(['resident', 'resident.hostel', 'resident.room'])->findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this receipt!'
                ], 403);
            }
        }

        $data = [
            'payment' => $payment,
            'resident' => $payment->resident,
            'hostel' => $payment->resident->hostel,
            'room' => $payment->resident->room,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.receipt', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('receipt-' . $payment->receipt_no . '.pdf');
    }

    public function pdfBulkReceipts(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payments = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->whereIn('id', $request->ids)
            ->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to view some receipts!'
                    ], 403);
                }
            }
        }

        $data = [
            'payments' => $payments,
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.bulk-receipts', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('bulk-receipts-' . date('Y-m-d') . '.pdf');
    }
}
