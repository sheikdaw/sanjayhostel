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
    // ============================================================
    // DISCOUNT HELPERS
    // ============================================================

    private function calculateDiscount($paymentDate)
    {
        $day = date('j', strtotime($paymentDate));
        if ($day <= 5) return 250;
        if ($day <= 10) return 125;
        return 0;
    }

    // ============================================================
    // PENDING HELPERS
    // ============================================================

    private function getPreviousPending($residentId, $month, $year)
    {
        return Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->sum('balance_amount');
    }

    private function getPreviousPendingDetails($residentId, $month, $year)
    {
        return Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
    }

    private function getPreviousMonthsList($residentId, $month, $year)
    {
        return Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')->orderBy('month', 'asc')
            ->get()
            ->map(function($p) {
                return date('F Y', mktime(0,0,0,$p->month,1,$p->year));
            })
            ->implode(', ');
    }

    // ============================================================
    // EXPORT HELPERS
    // ============================================================

    private function filterResidentsByMonth($query, $month, $year)
    {
        $startDate = date('Y-m-01', strtotime("$year-$month-01"));
        $endDate = date('Y-m-t', strtotime("$year-$month-01"));
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->where('joining_date', '<=', $endDate)
              ->where(function($sub) use ($startDate) {
                  $sub->whereNull('vacate_date')->orWhere('vacate_date', '>=', $startDate);
              });
        });
    }

    private function csvNumber($value) { 
        return number_format((float) $value, 2, '.', ''); 
    }
    
    private function csvString($value) { 
        if (is_null($value)) return '';
        $value = str_replace(',', ';', $value);
        return str_replace('"', '', $value);
    }

    // ============================================================
    // MAIN INDEX
    // ============================================================

    public function index()
    {
        $user = auth()->user();
        
        // Get hostels
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
            $residents = Resident::with(['hostel', 'room'])->where('status', 'ACTIVE')->orderBy('name')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
            $residents = Resident::with(['hostel', 'room'])->whereIn('hostel_id', $hostelIds)->where('status', 'ACTIVE')->orderBy('name')->get();
        }

        // Filters
        $filterMonth = request()->month ?? now()->month;
        $filterYear = request()->year ?? now()->year;
        $filterHostelId = request()->hostel_id ?? null;
        $filterStatus = request()->status ?? null;

        // Build query
        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $filterMonth)->where('year', $filterYear);

        if ($filterHostelId) {
            $query->whereHas('resident', function ($q) use ($filterHostelId) {
                $q->where('hostel_id', $filterHostelId);
            });
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Statistics
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

        // Monthly summary
        $monthlySummary = Payment::selectRaw('
            month, year, COUNT(*) as count, 
            SUM(rent_amount) as total_rent, 
            SUM(balance_amount) as total_balance, 
            SUM(cash_paid_amount + upi_paid_amount) as total_collected
        ')->where('month', $filterMonth)->where('year', $filterYear);

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
            ->orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        // Hostel wise summary
        $hostelSummaryQuery = Payment::with('resident.hostel')
            ->where('month', $filterMonth)->where('year', $filterYear);

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

        $hostelSummary = $hostelSummaryQuery->get()->groupBy('resident.hostel_id')->map(function ($group) {
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

        $filterMonthName = date('F', mktime(0, 0, 0, $filterMonth, 1));
        $filterHostelName = $filterHostelId ? Hostel::find($filterHostelId)->hostel_name ?? 'All Hostels' : 'All Hostels';

        return view('admin.payments.index', compact(
            'payments', 'hostels', 'stats', 'pendingPayments', 'monthlySummary', 
            'residents', 'hostelSummary', 'user', 'filterMonth', 'filterYear', 
            'filterMonthName', 'filterHostelName', 'filterHostelId', 'filterStatus'
        ));
    }

    // ============================================================
    // GET PAYMENT DETAILS (AJAX)
    // ============================================================

    public function getPaymentDetails(Request $request)
    {
        try {
            $resident = Resident::find($request->resident_id);
            if (!$resident) {
                return response()->json(['success' => false, 'message' => 'Resident not found']);
            }

            $paymentDate = $request->payment_date;
            $month = $request->month;
            $year = $request->year;
            
            $rent = $resident->rent_amount ?? 0;
            $discount = $this->calculateDiscount($paymentDate);
            $currentDue = $rent - $discount;
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $totalDue = $currentDue + $previousPending;
            
            $totalPaid = $request->total_paid ?? 0;
            
            // Allocate payment: Previous → Current → Advance
            $remaining = $totalPaid;
            $previousPaid = min($remaining, $previousPending);
            $remaining -= $previousPaid;
            $currentPaid = min($remaining, $currentDue);
            $remaining -= $currentPaid;
            $advanceAmount = max(0, $remaining);
            
            $previousBalance = max(0, $previousPending - $previousPaid);
            $currentBalance = max(0, $currentDue - $currentPaid);
            $totalBalance = $previousBalance + $currentBalance;

            return response()->json([
                'success' => true,
                'data' => [
                    'rent' => $rent,
                    'discount' => $discount,
                    'discount_type' => $discount > 0 ? ($discount == 250 ? 'Early Bird (1st-5th)' : 'Early Payment (6th-10th)') : 'No discount',
                    'current_due' => $currentDue,
                    'previous_pending' => $previousPending,
                    'total_due' => $totalDue,
                    'total_paid' => $totalPaid,
                    'previous_paid' => $previousPaid,
                    'current_paid' => $currentPaid,
                    'advance_amount' => $advanceAmount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $currentBalance,
                    'total_balance' => $totalBalance,
                    'day' => date('j', strtotime($paymentDate)),
                    'payment_date' => $paymentDate,
                    'month' => $month,
                    'year' => $year
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ============================================================
    // STORE PAYMENT
    // ============================================================

    public function store(Request $request)
    {
        $user = auth()->user();
        $resident = Resident::find($request->resident_id);
        
        if (!$resident) {
            return response()->json(['success' => false, 'message' => 'Resident not found!'], 404);
        }

        // Permission check
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($resident->hostel_id, $hostelIds)) {
                return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
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
            'fine_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $paymentDate = $request->payment_date;
            $month = $request->month;
            $year = $request->year;

            // Check existing payment
            $existingPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)->where('year', $year)->first();

            if ($existingPayment && $existingPayment->status === 'PAID') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Payment already completed for " . date('F Y', mktime(0,0,0,$month,1,$year))
                ], 422);
            }

            // Get previous pending
            $previousPendingList = $this->getPreviousPendingDetails($resident->id, $month, $year);
            $totalPreviousPending = $previousPendingList->sum('balance_amount');
            
            $totalPaid = $request->cash_paid_amount + $request->upi_paid_amount;
            $fullRent = (float) ($resident->rent_amount ?? 0);
            $tentativeDiscount = (float) $this->calculateDiscount($paymentDate);
            $fine = (float) ($request->fine_amount ?? 0);

            // Check discount eligibility
            $hasPreviousPending = $totalPreviousPending > 0;
            $amountForCurrentMonth = $totalPaid - $totalPreviousPending;
            $canCoverFullRent = $amountForCurrentMonth >= $fullRent;

            // ✅ Apply discount ONLY if no previous pending AND can cover full rent
            if (!$hasPreviousPending && $canCoverFullRent) {
                $discount = $tentativeDiscount;
            } else {
                $discount = 0;
            }

            $currentDue = $fullRent - $discount + $fine;
            
            // Allocate payment: Previous → Current → Advance
            $remaining = $totalPaid;
            
            // 1. Clear previous pending
            $previousPaid = 0;
            foreach ($previousPendingList as $prevPayment) {
                if ($remaining <= 0) break;
                $prevBalance = $prevPayment->balance_amount;
                $payAmount = min($remaining, $prevBalance);
                
                $prevPayment->cash_paid_amount += $payAmount;
                $newBalance = $prevBalance - $payAmount;
                $prevPayment->balance_amount = max(0, $newBalance);
                $prevPayment->status = ($newBalance <= 0) ? 'PAID' : 'PARTIAL';
                if ($request->transaction_id && $payAmount > 0) {
                    $prevPayment->transaction_id = $request->transaction_id;
                }
                $prevPayment->save();
                
                $previousPaid += $payAmount;
                $remaining -= $payAmount;
            }

            $previousBalance = max(0, $totalPreviousPending - $previousPaid);
            
            // 2. Pay current month
            $currentPaid = min($remaining, $currentDue);
            $remaining -= $currentPaid;
            $currentBalance = max(0, $currentDue - $currentPaid);
            
            // 3. Advance payment
            $advanceAmount = max(0, $remaining);
            $totalBalance = $previousBalance + $currentBalance;

            // Determine status
            $status = 'PENDING';
            if ($totalBalance <= 0) $status = 'PAID';
            elseif ($totalPaid > 0) $status = 'PARTIAL';

            // Generate receipt
            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            // Create/Update payment
            if ($existingPayment) {
                $existingPayment->cash_paid_amount += $currentPaid;
                $existingPayment->balance_amount = $currentBalance;
                $existingPayment->status = $status;
                $existingPayment->payment_date = $paymentDate;
                $existingPayment->transaction_id = $request->transaction_id;
                $existingPayment->discount_amount = $discount;
                $existingPayment->save();
                $payment = $existingPayment;
            } else {
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

            $payment->load(['resident.hostel', 'resident.room']);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully! Receipt: ' . $receiptNo,
                'data' => [
                    'payment' => $payment,
                    'receipt_no' => $receiptNo,
                    'total_paid' => $totalPaid,
                    'total_balance' => $totalBalance,
                    'status' => $status,
                    'discount_applied' => $discount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // UNPAID RESIDENTS WITH DETAILS (CORE LOGIC)
    // ============================================================

    /**
     * Get unpaid details for a resident
     * ✅ DISCOUNT APPLIED TO ALL WITHOUT PREVIOUS PENDING
     */
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

        // Today's discount
        $todayDiscount = (float) $this->calculateDiscount(now()->toDateString());
        $rentAmount = (float) ($resident->rent_amount ?? 0);

        // ✅ SIMPLE LOGIC: Apply discount to ALL without previous pending
        if (!$hasPreviousPending) {
            $discountApplied = $todayDiscount;
            if ($currentPayment) {
                // Has payment - reduce balance
                $balance = (float) $currentPayment->balance_amount;
                $effectiveCurrentBalance = max(0, $balance - $todayDiscount);
            } else {
                // No payment - show discounted rent
                $effectiveCurrentBalance = $rentAmount - $todayDiscount;
            }
        } else {
            // Has previous pending - NO discount
            $discountApplied = 0;
            if ($currentPayment) {
                $effectiveCurrentBalance = (float) $currentPayment->balance_amount;
            } else {
                $effectiveCurrentBalance = $rentAmount;
            }
        }

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

        // Previous months details
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

    /**
     * Export Unpaid Payments with Details (CSV)
     */
    public function exportUnpaidWithDetails(Request $request)
    {
        $user = auth()->user();
        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

        try {
            // Get residents
            $residentsQuery = Resident::with(['hostel', 'room'])->where('status', 'ACTIVE');
            $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!empty($hostelIds)) $residentsQuery->whereIn('hostel_id', $hostelIds);
            }
            if ($hostelId) $residentsQuery->where('hostel_id', $hostelId);

            $residents = $residentsQuery->orderBy('name')->get();

            // Process each resident
            $unpaidResidents = [];
            $totalPreviousPending = 0;
            $totalCurrentBalance = 0;
            $totalDue = 0;
            $totalUnpaid = 0;
            $totalDiscount = 0;

            foreach ($residents as $resident) {
                $details = $this->getUnpaidResidentsWithDetails($resident, $month, $year);
                
                if ($details['total_due'] > 0 || $details['overall_status'] != 'PAID') {
                    $unpaidResidents[] = $details;
                    $totalPreviousPending += $details['total_previous_pending'];
                    $totalCurrentBalance += $details['current_balance'];
                    $totalDue += $details['total_due'];
                    $totalDiscount += $details['discount_applied'];
                    $totalUnpaid++;
                }
            }

            // Build CSV
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $hostelName = $hostelId ? (Hostel::find($hostelId)->hostel_name ?? 'All Hostels') : 'All Hostels';
            $today = now()->format('d M Y');
            $todayDiscount = $this->calculateDiscount(now()->toDateString());

            $csv = "============================================================\n";
            $csv .= "UNPAID PAYMENTS REPORT\n";
            $csv .= "============================================================\n";
            $csv .= "Report Month: {$monthName} {$year}\n";
            $csv .= "Generated On: {$today}\n";
            $csv .= "Today's Discount: ₹" . number_format($todayDiscount, 2) . " (Day " . date('j') . ")\n";
            $csv .= "Hostel: {$hostelName}\n";
            $csv .= "Total Unpaid Residents: {$totalUnpaid}\n";
            $csv .= "Total Due: ₹" . number_format($totalDue, 2) . "\n";
            $csv .= "============================================================\n\n";

            // Summary
            $csv .= "--- SUMMARY ---\n";
            $csv .= "Total Unpaid Residents,{$totalUnpaid}\n";
            $csv .= "Total Previous Pending,₹" . number_format($totalPreviousPending, 2) . "\n";
            $csv .= "Total Current Balance,₹" . number_format($totalCurrentBalance, 2) . "\n";
            $csv .= "Total Discount Applied,₹" . number_format($totalDiscount, 2) . "\n";
            $csv .= "Total Due,₹" . number_format($totalDue, 2) . "\n\n";

            // Resident wise details
            $csv .= "--- RESIDENT WISE DETAILS ---\n";
            $csv .= "S.No,Resident,Hostel,Room,Rent (₹),Discount (₹),Current Due (₹),Previous Pending (₹),Total Due (₹),Status,Remark\n";

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
                $csv .= $this->csvString($item['remark'] ?? '') . "\n";
                $serialNo++;
            }

            // Previous months pending details
            $csv .= "\n\n--- PREVIOUS MONTHS PENDING DETAILS ---\n";
            $csv .= "Resident,Month,Year,Rent (₹),Discount (₹),Paid (₹),Balance (₹),Status,Remark\n";

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

            $filename = 'unpaid-payments-' . date('Y-m-d') . '.csv';
            return response($csv)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Export Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Unpaid Summary (AJAX)
     */
    public function getUnpaidSummary(Request $request)
    {
        try {
            $month = $request->filled('month') ? $request->month : date('n');
            $year = $request->filled('year') ? $request->year : date('Y');
            $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

            $residentsQuery = Resident::with(['hostel', 'room'])->where('status', 'ACTIVE');
            $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

            if (auth()->user()->role !== 'admin') {
                $hostelIds = auth()->user()->hostel_ids ?? [];
                $residentsQuery->whereIn('hostel_id', $hostelIds);
            }
            if ($hostelId) $residentsQuery->where('hostel_id', $hostelId);

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
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ============================================================
    // OTHER CRUD METHODS
    // ============================================================

    public function getResidentsByRoom($roomId)
    {
        $user = auth()->user();
        $query = Resident::where('room_id', $roomId)->where('status', 'ACTIVE')->orderBy('name');
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }
        return response()->json(['success' => true, 'data' => $query->get(['id', 'name', 'resident_code', 'hostel_id', 'room_id', 'rent_amount'])]);
    }

    public function getPartialPaymentDetails($residentId, $month, $year)
    {
        $payment = Payment::where('resident_id', $residentId)
            ->where('month', $month)->where('year', $year)
            ->where('status', 'PARTIAL')->first();

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'No partial payment found']);
        }

        $totalPaid = ($payment->cash_paid_amount ?? 0) + ($payment->upi_paid_amount ?? 0);
        $totalAmount = $payment->rent_amount - ($payment->discount_amount ?? 0) + ($payment->fine_amount ?? 0);

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
                'balance_amount' => max(0, $totalAmount - $totalPaid),
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
                return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
            }
        }
        return response()->json(['success' => true, 'data' => $payment]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
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
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
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

        return response()->json(['success' => true, 'message' => 'Payment updated!', 'data' => $payment]);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $payment = Payment::findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
            }
        }

        $payment->delete();
        return response()->json(['success' => true, 'message' => 'Payment deleted!']);
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
        $hasPending = Payment::where('resident_id', $residentId)
            ->where(function ($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function ($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])->exists();

        return response()->json(['success' => true, 'has_pending' => $hasPending]);
    }

    public function getResidentPayments($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        $payments = $resident->payments()->orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
            'summary' => [
                'total_paid' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount'),
                'total_balance' => $payments->sum('balance_amount'),
                'pending_count' => $payments->where('status', 'PENDING')->count(),
                'partial_count' => $payments->where('status', 'PARTIAL')->count()
            ]
        ]);
    }

    public function getResidentDue($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        $pendingPayments = $resident->payments()
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')->orderBy('month', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $residentId,
                'resident_name' => $resident->name,
                'total_due' => $pendingPayments->sum('balance_amount'),
                'pending_count' => $pendingPayments->count(),
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
                return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
            }
        }

        $totalAmount = $payment->rent_amount - $payment->discount_amount + $payment->fine_amount;
        $payment->update([
            'cash_paid_amount' => $totalAmount - $payment->upi_paid_amount,
            'balance_amount' => 0,
            'status' => 'PAID'
        ]);

        return response()->json(['success' => true, 'message' => 'Marked as paid!', 'data' => $payment]);
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
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $created = [];
        $errors = [];

        foreach ($request->resident_ids as $residentId) {
            $resident = Resident::find($residentId);
            
            if ($user->role !== 'admin') {
                $hostelIds = $user->hostel_ids ?? [];
                if (!in_array($resident->hostel_id, $hostelIds)) {
                    $errors[] = "No permission for: {$resident->name}";
                    continue;
                }
            }

            $exists = Payment::where('resident_id', $residentId)
                ->where('month', $request->month)->where('year', $request->year)->exists();

            if ($exists) {
                $errors[] = "Payment exists for " . $resident->name;
                continue;
            }

            $previousPending = $this->getPreviousPending($residentId, $request->month, $request->year);
            if ($previousPending > 0) {
                $errors[] = "Previous pending for " . $resident->name . " (₹" . number_format($previousPending, 2) . ")";
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
            ]);

            $created[] = $payment;
        }

        return response()->json([
            'success' => true,
            'message' => count($created) . ' created, ' . count($errors) . ' errors.',
            'data' => $created,
            'errors' => $errors
        ]);
    }

    public function getMonthlySummary()
    {
        $summary = Payment::selectRaw('
            month, year, COUNT(*) as total_count, 
            SUM(rent_amount) as total_rent, 
            SUM(discount_amount) as total_discount, 
            SUM(fine_amount) as total_fine, 
            SUM(cash_paid_amount) as total_cash, 
            SUM(upi_paid_amount) as total_upi, 
            SUM(balance_amount) as total_balance, 
            SUM(cash_paid_amount + upi_paid_amount) as total_collected
        ')->groupBy('year', 'month')
          ->orderBy('year', 'desc')->orderBy('month', 'desc')
          ->limit(12)->get();

        return response()->json(['success' => true, 'data' => $summary]);
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
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $payments = Payment::whereIn('id', $request->ids)->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
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

        return response()->json(['success' => true, 'message' => $updated . ' updated!']);
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:payments,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $payments = Payment::whereIn('id', $request->ids)->get();

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            foreach ($payments as $payment) {
                if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                    return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
                }
            }
        }

        $deleted = Payment::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true, 'message' => $deleted . ' deleted!']);
    }

    // ============================================================
    // PDF EXPORTS (Optional - Keep as needed)
    // ============================================================

    public function pdfUnpaidWithDetails(Request $request)
    {
        $user = auth()->user();
        $month = $request->filled('month') ? $request->month : date('n');
        $year = $request->filled('year') ? $request->year : date('Y');
        $hostelId = $request->filled('hostel_id') ? $request->hostel_id : null;

        $residentsQuery = Resident::with(['hostel', 'room'])->where('status', 'ACTIVE');
        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }
        if ($hostelId) $residentsQuery->where('hostel_id', $hostelId);

        $residents = $residentsQuery->orderBy('name')->get();

        $unpaidResidents = [];
        $totalDue = 0;
        $totalUnpaid = 0;

        foreach ($residents as $resident) {
            $details = $this->getUnpaidResidentsWithDetails($resident, $month, $year);
            if ($details['total_due'] > 0 || $details['overall_status'] != 'PAID') {
                $unpaidResidents[] = $details;
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
            'generated_at' => now()->format('d M Y h:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.unpaid-with-details', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('unpaid-with-details-' . date('Y-m-d') . '.pdf');
    }
}