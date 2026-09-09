<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Calculate discount based on payment date
     */
    private function calculateDiscount($paymentDate)
    {
        $day = date('j', strtotime($paymentDate));
        if ($day <= 5) {
            return 250;
        } elseif ($day <= 10) {
            return 125;
        }
        return 0;
    }

    /**
     * Get previous pending payments with details (oldest first)
     */
    private function getPreviousPendingDetails($residentId, $month, $year)
    {
        $resident = Resident::find($residentId);
        if (!$resident) {
            return collect([]);
        }

        $pendingPayments = collect([]);
        
        for ($m = 1; $m < $month; $m++) {
            $startDate = date('Y-m-01', strtotime("$year-$m-01"));
            $endDate = date('Y-m-t', strtotime("$year-$m-01"));
            
            $wasActive = ($resident->joining_date <= $endDate) && 
                         (is_null($resident->vacate_date) || $resident->vacate_date >= $startDate);
            
            if (!$wasActive) {
                continue;
            }
            
            $payment = Payment::where('resident_id', $residentId)
                ->where('month', $m)
                ->where('year', $year)
                ->first();
                
            if ($payment && in_array($payment->status, ['PENDING', 'PARTIAL'])) {
                $pendingPayments->push($payment);
            }
        }
        
        $maxYearsBack = 3;
        for ($y = $year - 1; $y >= max(2000, $year - $maxYearsBack); $y--) {
            for ($m = 12; $m >= 1; $m--) {
                if ($y == $year && $m >= $month) {
                    continue;
                }
                
                $startDate = date('Y-m-01', strtotime("$y-$m-01"));
                $endDate = date('Y-m-t', strtotime("$y-$m-01"));
                
                $wasActive = ($resident->joining_date <= $endDate) && 
                             (is_null($resident->vacate_date) || $resident->vacate_date >= $startDate);
                
                if (!$wasActive) {
                    continue;
                }
                
                $payment = Payment::where('resident_id', $residentId)
                    ->where('month', $m)
                    ->where('year', $y)
                    ->first();
                    
                if ($payment && in_array($payment->status, ['PENDING', 'PARTIAL'])) {
                    $pendingPayments->push($payment);
                }
            }
        }
        
        return $pendingPayments->sortBy(function($item) {
            return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
        })->values();
    }

    /**
     * Get previous pending total
     */
    private function getPreviousPending($residentId, $month, $year)
    {
        $resident = Resident::find($residentId);
        if (!$resident) {
            return 0;
        }

        $pendingTotal = 0;
        $maxYearsBack = 3;
        
        for ($m = 1; $m < $month; $m++) {
            $payment = Payment::where('resident_id', $residentId)
                ->where('month', $m)
                ->where('year', $year)
                ->first();
                
            $startDate = date('Y-m-01', strtotime("$year-$m-01"));
            $endDate = date('Y-m-t', strtotime("$year-$m-01"));
            
            $wasActive = ($resident->joining_date <= $endDate) && 
                         (is_null($resident->vacate_date) || $resident->vacate_date >= $startDate);
            
            if (!$wasActive) {
                continue;
            }
            
            if ($payment && in_array($payment->status, ['PENDING', 'PARTIAL'])) {
                $pendingTotal += $payment->balance_amount;
            }
        }
        
        for ($y = $year - 1; $y >= max(2000, $year - $maxYearsBack); $y--) {
            for ($m = 12; $m >= 1; $m--) {
                if ($y == $year && $m >= $month) {
                    continue;
                }
                
                $payment = Payment::where('resident_id', $residentId)
                    ->where('month', $m)
                    ->where('year', $y)
                    ->first();
                    
                $startDate = date('Y-m-01', strtotime("$y-$m-01"));
                $endDate = date('Y-m-t', strtotime("$y-$m-01"));
                
                $wasActive = ($resident->joining_date <= $endDate) && 
                             (is_null($resident->vacate_date) || $resident->vacate_date >= $startDate);
                
                if (!$wasActive) {
                    continue;
                }
                
                if ($payment && in_array($payment->status, ['PENDING', 'PARTIAL'])) {
                    $pendingTotal += $payment->balance_amount;
                }
            }
        }
        
        return $pendingTotal;
    }

    /**
     * Filter residents who were active during the selected month
     */
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

    /**
     * Build detailed response message
     */
    private function buildDetailedResponseMessage($totalPaid, $previousPaid, $currentPaid, $advanceAmount, $previousBalance, $currentBalance, $totalBalance, $totalPreviousPending, $previousClearedCount, $receiptNo, $isExisting = false)
    {
        $messages = [];
        $messages[] = $isExisting ? "✅ Payment updated successfully!" : "✅ Payment recorded successfully!";
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
        } else {
            if ($currentBalance > 0) {
                $messages[] = "📅 Current month not paid: ₹" . number_format($currentBalance, 2) . " pending";
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
     * Display a listing of payments with filters
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
        }

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

        $filterMonth = $request->month ?? now()->month;
        $filterYear = $request->year ?? now()->year;
        $filterHostelId = $request->hostel_id ?? null;
        $filterStatus = $request->status ?? null;
        $search = $request->search ?? null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $filterMonth, $filterYear);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($filterHostelId) {
            $residentsQuery->where('hostel_id', $filterHostelId);
        }

        if ($search) {
            $residentsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('resident_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $activeResidents = $residentsQuery->orderBy('name')->get();
        $residentIds = $activeResidents->pluck('id')->toArray();

        $paymentsQuery = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->whereIn('resident_id', $residentIds);

        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $combinedData = [];
        $stats = [
            'total' => 0,
            'pending' => 0,
            'paid' => 0,
            'partial' => 0,
            'unpaid' => 0,
            'total_rent' => 0,
            'total_discount' => 0,
            'total_fine' => 0,
            'total_cash' => 0,
            'total_upi' => 0,
            'total_balance' => 0,
            'total_collected' => 0
        ];

        foreach ($activeResidents as $resident) {
            $payment = $payments->get($resident->id);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            $previousPending = $this->getPreviousPending($resident->id, $filterMonth, $filterYear);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            // ✅ Status Logic
            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment) {
                if ($payment->status === 'PAID' && $currentBalance == 0) {
                    $status = 'PAID';
                } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                    $status = 'PARTIAL';
                } elseif ($payment->status === 'PENDING') {
                    $status = 'PENDING';
                } else {
                    $status = $payment->status;
                }
            } else {
                $status = 'UNPAID';
            }

            if ($filterStatus) {
                if ($filterStatus === 'UNPAID') {
                    if ($status !== 'UNPAID') continue;
                } elseif ($filterStatus === 'PENDING') {
                    if ($status !== 'PENDING') continue;
                } elseif ($filterStatus === 'PARTIAL') {
                    if ($status !== 'PARTIAL') continue;
                } elseif ($filterStatus === 'PAID') {
                    if ($status !== 'PAID') continue;
                }
            }

            $combinedData[] = (object) [
                'id' => $payment ? $payment->id : null,
                'resident_id' => $resident->id,
                'receipt_no' => $payment ? $payment->receipt_no : 'N/A',
                'resident' => $resident,
                'month' => $filterMonth,
                'year' => $filterYear,
                'rent_amount' => $payment ? $payment->rent_amount : $rentAmount,
                'discount_amount' => $payment ? $payment->discount_amount : 0,
                'fine_amount' => $payment ? $payment->fine_amount : 0,
                'cash_paid_amount' => $payment ? $payment->cash_paid_amount : 0,
                'upi_paid_amount' => $payment ? $payment->upi_paid_amount : 0,
                'balance_amount' => $totalDue,
                'current_balance_amount' => $currentBalance,
                'payment_date' => $payment ? $payment->payment_date : now(),
                'transaction_id' => $payment ? $payment->transaction_id : null,
                'status' => $status,
                'payment_type' => $payment ? $payment->payment_type : null,
                'previous_pending_cleared' => $payment ? $payment->previous_pending_cleared : 0,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded'),
                'created_at' => $payment ? $payment->created_at : now(),
                'joining_date' => $resident->joining_date,
                'vacate_date' => $resident->vacate_date,
                'has_previous_pending' => $previousPending > 0,
                'previous_pending_amount' => $previousPending,
                'is_unpaid' => (!$payment && $previousPending == 0),
            ];

            $stats['total']++;
            if ($status === 'PAID') $stats['paid']++;
            elseif ($status === 'PENDING') $stats['pending']++;
            elseif ($status === 'PARTIAL') $stats['partial']++;
            elseif ($status === 'UNPAID') $stats['unpaid']++;

            $rent = $payment ? $payment->rent_amount : $rentAmount;
            $stats['total_rent'] += $rent;
            $stats['total_discount'] += $payment ? $payment->discount_amount : 0;
            $stats['total_fine'] += $payment ? $payment->fine_amount : 0;
            $stats['total_cash'] += $payment ? $payment->cash_paid_amount : 0;
            $stats['total_upi'] += $payment ? $payment->upi_paid_amount : 0;
            $stats['total_balance'] += $totalDue;
            
            if ($payment) {
                $stats['total_collected'] += ($payment->cash_paid_amount + $payment->upi_paid_amount);
            }
        }

        usort($combinedData, function($a, $b) {
            return strcmp($a->resident->name ?? '', $b->resident->name ?? '');
        });

        $pendingPayments = collect($combinedData)->filter(function($item) {
            return in_array($item->status, ['PENDING', 'UNPAID', 'PARTIAL']);
        });

        $filterMonthName = date('F', mktime(0, 0, 0, $filterMonth, 1));
        $filterHostelName = $filterHostelId ? (Hostel::find($filterHostelId)->hostel_name ?? 'All Hostels') : 'All Hostels';
        $rooms = Room::where('status', 'ACTIVE')->get();

        return view('admin.payments.index', compact(
            'combinedData',
            'hostels',
            'residents',
            'rooms',
            'stats',
            'pendingPayments',
            'user',
            'filterMonth',
            'filterYear',
            'filterMonthName',
            'filterHostelName',
            'filterHostelId',
            'filterStatus',
            'search',
            'activeResidents'
        ));
    }

    /**
     * Filter payments via AJAX - No page refresh
     */
    public function filter(Request $request)
    {
        $user = auth()->user();

        $filterMonth = $request->month ?? now()->month;
        $filterYear = $request->year ?? now()->year;
        $filterHostelId = $request->hostel_id ?? null;
        $filterStatus = $request->status ?? null;
        $search = $request->search ?? null;

        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $filterMonth, $filterYear);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($filterHostelId) {
            $residentsQuery->where('hostel_id', $filterHostelId);
        }

        if ($search) {
            $residentsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('resident_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $activeResidents = $residentsQuery->orderBy('name')->get();
        $residentIds = $activeResidents->pluck('id')->toArray();

        $paymentsQuery = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $filterMonth)
            ->where('year', $filterYear)
            ->whereIn('resident_id', $residentIds);

        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $combinedData = [];
        $stats = [
            'total' => 0, 'pending' => 0, 'paid' => 0, 'partial' => 0,
            'unpaid' => 0, 'total_rent' => 0, 'total_discount' => 0,
            'total_fine' => 0, 'total_cash' => 0, 'total_upi' => 0,
            'total_balance' => 0, 'total_collected' => 0
        ];

        $pendingCount = 0;

        foreach ($activeResidents as $resident) {
            $payment = $payments->get($resident->id);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            $previousPending = $this->getPreviousPending($resident->id, $filterMonth, $filterYear);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment) {
                if ($payment->status === 'PAID' && $currentBalance == 0) {
                    $status = 'PAID';
                } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                    $status = 'PARTIAL';
                } elseif ($payment->status === 'PENDING') {
                    $status = 'PENDING';
                } else {
                    $status = $payment->status;
                }
            } else {
                $status = 'UNPAID';
            }

            if ($filterStatus) {
                if ($filterStatus === 'UNPAID') {
                    if ($status !== 'UNPAID') continue;
                } elseif ($filterStatus === 'PENDING') {
                    if ($status !== 'PENDING') continue;
                } elseif ($filterStatus === 'PARTIAL') {
                    if ($status !== 'PARTIAL') continue;
                } elseif ($filterStatus === 'PAID') {
                    if ($status !== 'PAID') continue;
                }
            }

            $combinedData[] = [
                'id' => $payment ? $payment->id : null,
                'resident_id' => $resident->id,
                'receipt_no' => $payment ? $payment->receipt_no : 'N/A',
                'resident_name' => $resident->name ?? 'N/A',
                'resident_code' => $resident->resident_code ?? '',
                'room_no' => $resident->room->room_no ?? 'N/A',
                'hostel_name' => $resident->hostel->hostel_name ?? 'N/A',
                'month' => $filterMonth,
                'year' => $filterYear,
                'month_name' => date('F', mktime(0, 0, 0, $filterMonth, 1)),
                'rent_amount' => number_format($payment ? $payment->rent_amount : $rentAmount, 2),
                'discount_amount' => number_format($payment ? $payment->discount_amount : 0, 2),
                'fine_amount' => number_format($payment ? $payment->fine_amount : 0, 2),
                'cash_paid_amount' => number_format($payment ? $payment->cash_paid_amount : 0, 2),
                'upi_paid_amount' => number_format($payment ? $payment->upi_paid_amount : 0, 2),
                'balance_amount' => number_format($totalDue, 2),
                'current_balance_amount' => number_format($currentBalance, 2),
                'total_paid' => number_format($currentPaid, 2),
                'status' => $status,
                'status_badge' => strtolower($status),
                'payment_type' => $payment ? $payment->payment_type : null,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded'),
                'payment_date' => $payment ? $payment->payment_date : now(),
                'has_previous_pending' => $previousPending > 0,
                'previous_pending_amount' => number_format($previousPending, 2),
                'previous_pending_cleared' => $payment ? $payment->previous_pending_cleared : 0,
            ];

            $stats['total']++;
            if ($status === 'PAID') $stats['paid']++;
            elseif ($status === 'PENDING') $stats['pending']++;
            elseif ($status === 'PARTIAL') $stats['partial']++;
            elseif ($status === 'UNPAID') $stats['unpaid']++;

            $rent = $payment ? $payment->rent_amount : $rentAmount;
            $stats['total_rent'] += $rent;
            $stats['total_discount'] += $payment ? $payment->discount_amount : 0;
            $stats['total_fine'] += $payment ? $payment->fine_amount : 0;
            $stats['total_cash'] += $payment ? $payment->cash_paid_amount : 0;
            $stats['total_upi'] += $payment ? $payment->upi_paid_amount : 0;
            $stats['total_balance'] += $totalDue;
            
            if ($payment) {
                $stats['total_collected'] += ($payment->cash_paid_amount + $payment->upi_paid_amount);
            }

            if (in_array($status, ['PENDING', 'UNPAID', 'PARTIAL'])) {
                $pendingCount++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $combinedData,
            'stats' => $stats,
            'filter_month' => date('F', mktime(0, 0, 0, $filterMonth, 1)),
            'filter_year' => $filterYear,
            'pending_count' => $pendingCount,
            'total_records' => count($combinedData)
        ]);
    }

    /**
     * Store a newly created payment
     * ✅ Status is automatically calculated
     */
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
                    'message' => 'Permission denied!'
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
            'fine_amount' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|in:cash,upi,both'
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
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            $fineAmount = (float) ($request->fine_amount ?? 0);
            $cashPaid = (float) $request->cash_paid_amount;
            $upiPaid = (float) $request->upi_paid_amount;
            $totalPaid = $cashPaid + $upiPaid; // Money actually received

            if ($totalPaid <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ No payment amount entered! Please enter at least ₹1.'
                ], 422);
            }

            // Get previous pending
            $previousPendingList = $this->getPreviousPendingDetails($resident->id, $month, $year);
            $totalPreviousPending = $previousPendingList->sum('balance_amount');

            $existingPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            // DISCOUNT LOGIC
            $tentativeDiscount = (float) $this->calculateDiscount($paymentDate);
            $totalNeedToPayWithDiscount = $rentAmount + $totalPreviousPending + $fineAmount - $tentativeDiscount;

            $existingPaidAmount = $existingPayment ? (float) ($existingPayment->cash_paid_amount + $existingPayment->upi_paid_amount) : 0;
            $totalPaidIncludingExisting = $totalPaid + $existingPaidAmount;

            if ($totalNeedToPayWithDiscount <= $totalPaidIncludingExisting) {
                $discount = $tentativeDiscount;
                $discountApplied = true;
                $discountReason = "✅ Discount applied: ₹" . number_format($discount, 2);
            } else {
                $discount = 0;
                $discountApplied = false;
                $discountReason = "❌ No discount: Need ₹" . number_format($totalNeedToPayWithDiscount, 2) . ", paid ₹" . number_format($totalPaidIncludingExisting, 2);
            }

            $totalNeedToPay = $rentAmount + $totalPreviousPending + $fineAmount - $discount;
            $toPay = $totalPaid;

            // Generate receipt
            $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            while (Payment::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }

            // Allocate payment: Previous → Current → Advance
            $previousPaid = 0;
            $currentPaid = 0;
            $advanceAmount = 0;
            $previousClearedCount = 0;
            $remaining = $toPay;

            // Step 1: Clear previous pending (oldest first)
            foreach ($previousPendingList as $prevPayment) {
                if ($remaining <= 0) break;

                $prevBalance = $prevPayment->balance_amount;
                $payAmount = min($remaining, $prevBalance);
                
                if ($payAmount > 0) {
                    $prevPayment->cash_paid_amount += $payAmount;
                    $newBalance = $prevBalance - $payAmount;
                    $prevPayment->balance_amount = max(0, $newBalance);
                    $prevPayment->status = ($newBalance <= 0) ? 'PAID' : 'PARTIAL';

                    if ($request->transaction_id && $payAmount > 0) {
                        $prevPayment->transaction_id = $prevPayment->transaction_id 
                            ? $prevPayment->transaction_id . ' / ' . $request->transaction_id 
                            : $request->transaction_id;
                    }

                    $monthName = date('F Y', mktime(0,0,0,$prevPayment->month,1,$prevPayment->year));
                    $prevPayment->remark = ($newBalance <= 0)
                        ? "✅ {$monthName} cleared using " . date('F Y', mktime(0,0,0,$month,1,$year)) . " payment"
                        : "🟡 Partial cleared {$monthName}: ₹" . number_format($payAmount, 2);

                    $prevPayment->save();
                    $previousPaid += $payAmount;
                    $remaining -= $payAmount;
                    if ($newBalance <= 0) $previousClearedCount++;
                }
            }

            $previousBalance = max(0, $totalPreviousPending - $previousPaid);

            // Step 2: Pay current month
            $currentDue = $rentAmount + $fineAmount - $discount;
            $existingCurrentPaid = $existingPayment ? (float) ($existingPayment->cash_paid_amount + $existingPayment->upi_paid_amount) : 0;
            $remainingCurrentDue = max(0, $currentDue - $existingCurrentPaid);
            
            $currentPaidThisTransaction = min($remaining, $remainingCurrentDue);
            $currentPaid = $existingCurrentPaid + $currentPaidThisTransaction;
            $remaining -= $currentPaidThisTransaction;
            $currentBalance = max(0, $currentDue - $currentPaid);

            // Step 3: Advance
            $advanceAmount = max(0, $remaining);
            $totalBalance = $previousBalance + $currentBalance;

            // ✅ Status is automatically calculated
            if ($previousBalance > 0) {
                $status = 'PENDING';
            } elseif ($currentPaid > 0 && $currentBalance == 0) {
                $status = 'PAID';
            } elseif ($currentPaid > 0 && $currentBalance > 0) {
                $status = 'PARTIAL';
            } else {
                $status = 'UNPAID';
            }

            // Build remark
            $monthName = date('F Y', mktime(0,0,0,$month,1,$year));
            $remark = $discountReason . " | ";
            
            if ($fineAmount > 0) {
                $remark .= "💰 Fine ₹" . number_format($fineAmount, 2) . " | ";
            }
            
            if ($previousPaid > 0) {
                $remark .= "✅ Previous cleared ₹" . number_format($previousPaid, 2) . " | ";
            }
            if ($previousBalance > 0) {
                $remark .= "⚠️ Previous remaining ₹" . number_format($previousBalance, 2) . " | ";
            }
            
            if ($currentPaidThisTransaction > 0) {
                $remark .= ($currentBalance <= 0) ? "✅ {$monthName} paid ₹" . number_format($currentPaidThisTransaction, 2) : "🟡 {$monthName} partial ₹" . number_format($currentPaidThisTransaction, 2);
            } else {
                if ($existingCurrentPaid > 0 && $currentBalance == 0) {
                    $remark .= "✅ {$monthName} already fully paid";
                } elseif ($existingCurrentPaid > 0 && $currentBalance > 0) {
                    $remark .= "🟡 {$monthName} already partially paid (₹" . number_format($existingCurrentPaid, 2) . ")";
                } else {
                    $remark .= "❌ {$monthName} not paid";
                }
            }
            
            $remark .= $advanceAmount > 0 ? " | 💰 Advance ₹" . number_format($advanceAmount, 2) : "";
            $remark .= $totalBalance > 0 ? " | 📊 Pending ₹" . number_format($totalBalance, 2) : " | ✅ All cleared!";

            // Determine payment type
            $paymentType = $request->payment_type ?? 'both';
            if ($cashPaid > 0 && $upiPaid > 0) $paymentType = 'both';
            elseif ($cashPaid > 0) $paymentType = 'cash';
            elseif ($upiPaid > 0) $paymentType = 'upi';

            // ✅ Create or update payment record
            if ($totalPaid > 0) {
                if ($existingPayment) {
                    $existingPayment->cash_paid_amount += $cashPaid;
                    $existingPayment->upi_paid_amount += $upiPaid;
                    $existingPayment->balance_amount = $currentBalance;
                    $existingPayment->status = $status;
                    $existingPayment->payment_date = $paymentDate;
                    $existingPayment->discount_amount = $discount;
                    $existingPayment->fine_amount = $fineAmount;
                    $existingPayment->remark = $remark;
                    $existingPayment->payment_type = $paymentType;
                    if ($request->transaction_id) {
                        $existingPayment->transaction_id = $existingPayment->transaction_id 
                            ? $existingPayment->transaction_id . ' / ' . $request->transaction_id 
                            : $request->transaction_id;
                    }
                    $existingPayment->save();
                    $payment = $existingPayment;
                } else {
                    $payment = Payment::create([
                        'resident_id' => $resident->id,
                        'receipt_no' => $receiptNo,
                        'month' => $month,
                        'year' => $year,
                        'rent_amount' => $rentAmount,
                        'discount_amount' => $discount,
                        'fine_amount' => $fineAmount,
                        'cash_paid_amount' => $cashPaid,
                        'upi_paid_amount' => $upiPaid,
                        'balance_amount' => $currentBalance,
                        'payment_date' => $paymentDate,
                        'transaction_id' => $request->transaction_id,
                        'status' => $status,
                        'payment_type' => $paymentType,
                        'previous_pending_cleared' => $previousPaid,
                        'remark' => $remark,
                    ]);
                }
            }

            DB::commit();

            $message = $this->buildDetailedResponseMessage(
                $totalPaid, $previousPaid, $currentPaidThisTransaction, $advanceAmount,
                $previousBalance, $currentBalance, $totalBalance,
                $totalPreviousPending, $previousClearedCount, $receiptNo,
                $existingPayment ? true : false
            );

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'payment' => $payment,
                    'receipt_no' => $receiptNo,
                    'total_paid' => $totalPaid,
                    'discount_applied' => $discount,
                    'discount_eligible' => $discountApplied,
                    'status' => $status,
                    'remark' => $remark,
                    'calculation' => [
                        'rent' => $rentAmount,
                        'fine' => $fineAmount,
                        'discount' => $discount,
                        'previous_pending' => $totalPreviousPending,
                        'customer_paid' => $toPay,
                        'scenario' => $totalBalance > 0 ? 'Partial' : 'Paid'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit payment - Returns payment data for editing
     */
    public function edit($id)
    {
        $user = auth()->user();
        $payment = Payment::with(['resident', 'resident.hostel', 'resident.room'])->findOrFail($id);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            if (!in_array($payment->resident->hostel_id, $hostelIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission denied!'
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update payment - Uses same logic as store
     * ✅ Status is automatically recalculated
     */
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
            'year' => 'required|integer|min:2000',
            'rent_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
            'cash_paid_amount' => 'required|numeric|min:0',
            'upi_paid_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string|max:255',
            'payment_type' => 'nullable|in:cash,upi,both'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $cashPaid = (float) $request->cash_paid_amount;
        $upiPaid = (float) $request->upi_paid_amount;
        $totalPaid = $cashPaid + $upiPaid;
        $fineAmount = (float) ($request->fine_amount ?? 0);
        $discount = (float) ($request->discount_amount ?? 0);
        $rentAmount = (float) $request->rent_amount;
        
        $totalAmount = $rentAmount + $fineAmount - $discount;
        $balanceAmount = max(0, $totalAmount - $totalPaid);

        // ✅ Status is automatically calculated
        if ($balanceAmount == 0 && $totalPaid > 0) {
            $status = 'PAID';
        } elseif ($totalPaid > 0 && $balanceAmount > 0) {
            $status = 'PARTIAL';
        } elseif ($totalPaid == 0 && $balanceAmount > 0) {
            $status = 'PENDING';
        } else {
            $status = 'PENDING';
        }

        $paymentType = $request->payment_type ?? 'both';
        if ($cashPaid > 0 && $upiPaid > 0) $paymentType = 'both';
        elseif ($cashPaid > 0) $paymentType = 'cash';
        elseif ($upiPaid > 0) $paymentType = 'upi';

        $monthName = date('F Y', mktime(0,0,0,$request->month,1,$request->year));
        $oldRemark = $payment->remark ?? '';
        
        $newRemark = "🔄 Updated on " . date('d M Y H:i') . " | ";
        $newRemark .= "Month: {$monthName} | ";
        $newRemark .= "Rent: ₹" . number_format($rentAmount, 2) . " | ";
        $newRemark .= $discount > 0 ? "Discount: ₹" . number_format($discount, 2) . " | " : "";
        $newRemark .= $fineAmount > 0 ? "Fine: ₹" . number_format($fineAmount, 2) . " | " : "";
        $newRemark .= "Cash: ₹" . number_format($cashPaid, 2) . " | ";
        $newRemark .= "UPI: ₹" . number_format($upiPaid, 2) . " | ";
        $newRemark .= "Total Paid: ₹" . number_format($totalPaid, 2) . " | ";
        $newRemark .= $balanceAmount > 0 ? "Balance: ₹" . number_format($balanceAmount, 2) : "✅ Fully Paid";
        $newRemark .= " | Status: " . $status;
        $newRemark .= " | Method: " . strtoupper($paymentType);
        
        $finalRemark = $newRemark;
        if (!empty($oldRemark) && strlen($oldRemark) < 500) {
            $finalRemark .= " | [Previous: " . $oldRemark . "]";
        }

        $payment->update([
            'month' => $request->month,
            'year' => $request->year,
            'rent_amount' => $rentAmount,
            'discount_amount' => $discount,
            'fine_amount' => $fineAmount,
            'cash_paid_amount' => $cashPaid,
            'upi_paid_amount' => $upiPaid,
            'balance_amount' => $balanceAmount,
            'payment_date' => $request->payment_date,
            'transaction_id' => $request->transaction_id,
            'status' => $status,
            'payment_type' => $paymentType,
            'remark' => $finalRemark
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Payment updated successfully!',
            'data' => $payment->fresh()
        ]);
    }

    /**
     * Delete payment
     */
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

    /**
     * Get residents by room
     */
    public function getResidentsByRoom($roomId)
    {
        $user = auth()->user();
        $query = Resident::where('room_id', $roomId)->where('status', 'ACTIVE')->orderBy('name');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereIn('hostel_id', $hostelIds);
        }

        return response()->json(['success' => true, 'data' => $query->get(['id', 'name', 'resident_code', 'rent_amount'])]);
    }

    /**
     * Get resident rent
     */
    public function getResidentRent($residentId)
    {
        $resident = Resident::findOrFail($residentId);
        return response()->json([
            'success' => true,
            'data' => [
                'rent_amount' => $resident->rent_amount ?? 0,
                'name' => $resident->name
            ]
        ]);
    }

    /**
     * Check if already paid
     */
    public function checkAlreadyPaid($residentId, $month, $year)
    {
        $payment = Payment::where('resident_id', $residentId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($payment && in_array($payment->status, ['PAID', 'PARTIAL'])) {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'receipt_no' => $payment->receipt_no,
                'amount' => $payment->rent_amount,
                'cash_paid' => $payment->cash_paid_amount,
                'upi_paid' => $payment->upi_paid_amount,
                'payment_type' => $payment->payment_type,
                'balance' => $payment->balance_amount
            ]);
        }

        return response()->json(['success' => true, 'is_paid' => false]);
    }

    /**
     * Check previous pending
     */
    public function checkPreviousPending($residentId, $month, $year)
    {
        $hasPending = Payment::where('resident_id', $residentId)
            ->where(function($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->exists();

        return response()->json([
            'success' => true,
            'has_pending' => $hasPending
        ]);
    }

    /**
     * Get payment details for preview
     */
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
            $cashPaid = (float) ($request->cash_paid_amount ?? 0);
            $upiPaid = (float) ($request->upi_paid_amount ?? 0);
            $fineAmount = (float) ($request->fine_amount ?? 0);
            $totalPaid = $cashPaid + $upiPaid;

            $existingPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $tentativeDiscount = $this->calculateDiscount($paymentDate);
            $totalPreviousPending = $this->getPreviousPending($resident->id, $month, $year);

            $existingPaidAmount = $existingPayment ? (float) ($existingPayment->cash_paid_amount + $existingPayment->upi_paid_amount) : 0;
            $totalPaidIncludingExisting = $totalPaid + $existingPaidAmount;

            $totalNeedToPayWithDiscount = $resident->rent_amount + $totalPreviousPending + $fineAmount - $tentativeDiscount;

            if ($totalNeedToPayWithDiscount <= $totalPaidIncludingExisting) {
                $discount = $tentativeDiscount;
                $discountApplied = true;
            } else {
                $discount = 0;
                $discountApplied = false;
            }

            $currentDue = $resident->rent_amount + $fineAmount - $discount;
            $existingCurrentPaid = $existingPayment ? (float) ($existingPayment->cash_paid_amount + $existingPayment->upi_paid_amount) : 0;
            
            $remaining = $totalPaid;
            $previousPaid = min($remaining, $totalPreviousPending);
            $remaining -= $previousPaid;
            
            $remainingCurrentDue = max(0, $currentDue - $existingCurrentPaid);
            $currentPaid = min($remaining, $remainingCurrentDue);
            $remaining -= $currentPaid;
            $advanceAmount = max(0, $remaining);

            $previewRemark = ($discountApplied ? "✅ Discount ₹" . number_format($discount, 2) : "❌ No discount") . " | ";
            $previewRemark .= $fineAmount > 0 ? "Fine ₹" . number_format($fineAmount, 2) . " | " : "";
            $previewRemark .= $previousPaid > 0 ? "Previous: ₹" . number_format($previousPaid, 2) . " | " : "";
            $previewRemark .= $currentPaid > 0 ? "Current: ₹" . number_format($currentPaid, 2) : "Current: ₹0";
            $previewRemark .= $advanceAmount > 0 ? " | Advance: ₹" . number_format($advanceAmount, 2) : "";

            return response()->json([
                'success' => true,
                'data' => [
                    'rent' => $resident->rent_amount,
                    'fine' => $fineAmount,
                    'discount' => $discount,
                    'previous_pending' => $totalPreviousPending,
                    'current_due' => $currentDue,
                    'total_due' => $currentDue + $totalPreviousPending,
                    'total_paid' => $totalPaid,
                    'existing_paid' => $existingPaidAmount,
                    'previous_paid' => $previousPaid,
                    'current_paid' => $currentPaid,
                    'advance_amount' => $advanceAmount,
                    'preview_remark' => $previewRemark,
                    'discount_eligible' => $discountApplied
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get rooms by hostel for filter
     */
    public function getRoomsByHostel($hostelId)
    {
        $rooms = Room::where('hostel_id', $hostelId)
            ->where('status', 'ACTIVE')
            ->orderBy('room_no')
            ->get(['id', 'room_no']);

        return response()->json(['success' => true, 'data' => $rooms]);
    }

    /**
     * Bulk payment creation
     */
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

        $created = 0;
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
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();

            if ($exists) {
                $errors[] = "Already exists for: " . $resident->name;
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

            $rentAmount = (float) ($resident->rent_amount ?? 0);

            Payment::create([
                'resident_id' => $residentId,
                'receipt_no' => $receiptNo,
                'month' => $request->month,
                'year' => $request->year,
                'rent_amount' => $rentAmount,
                'discount_amount' => 0,
                'fine_amount' => 0,
                'cash_paid_amount' => 0,
                'upi_paid_amount' => 0,
                'balance_amount' => $rentAmount,
                'payment_date' => $request->payment_date,
                'status' => 'PENDING',
                'payment_type' => 'none',
                'remark' => "📅 Pending for " . date('F', mktime(0,0,0,$request->month,1)) . " " . $request->year
            ]);

            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => $created . ' payments created. ' . count($errors) . ' errors.',
            'errors' => $errors
        ]);
    }

    /**
     * Bulk status update
     */
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
                if ($payment->balance_amount <= 0) {
                    $payment->status = 'PAID';
                } else {
                    continue;
                }
            } elseif ($request->status === 'PENDING') {
                if ($payment->balance_amount > 0) {
                    $payment->status = 'PENDING';
                } else {
                    continue;
                }
            } elseif ($request->status === 'PARTIAL') {
                if ($payment->balance_amount > 0 && ($payment->cash_paid_amount + $payment->upi_paid_amount) > 0) {
                    $payment->status = 'PARTIAL';
                } else {
                    continue;
                }
            }
            $payment->save();
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => $updated . ' payments updated to ' . $request->status
        ]);
    }

    /**
     * Bulk delete
     */
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

        return response()->json([
            'success' => true,
            'message' => $deleted . ' payments deleted!'
        ]);
    }

    // ============================================================
    // EXPORT METHODS (All use same logic)
    // ============================================================

    /**
     * Export filtered payments as CSV
     */
    public function exportFiltered(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;
        $filterStatus = $request->status ?? null;
        $search = $request->search ?? null;

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

        if ($search) {
            $residentsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('resident_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $activeResidents = $residentsQuery->orderBy('name')->get();
        $residentIds = $activeResidents->pluck('id')->toArray();

        $paymentsQuery = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $month)
            ->where('year', $year)
            ->whereIn('resident_id', $residentIds);

        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $exportData = [];

        foreach ($activeResidents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment) {
                if ($payment->status === 'PAID' && $currentBalance == 0) {
                    $status = 'PAID';
                } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                    $status = 'PARTIAL';
                } elseif ($payment->status === 'PENDING') {
                    $status = 'PENDING';
                } else {
                    $status = $payment->status;
                }
            } else {
                $status = 'UNPAID';
            }

            if ($filterStatus && $filterStatus !== $status) {
                continue;
            }

            $exportData[] = [
                'receipt_no' => $payment ? $payment->receipt_no : 'N/A',
                'resident_name' => $resident->name ?? 'N/A',
                'hostel_name' => $resident->hostel->hostel_name ?? 'N/A',
                'room_no' => $resident->room->room_no ?? 'N/A',
                'month' => date('F', mktime(0,0,0,$month,1)),
                'year' => $year,
                'rent_amount' => $payment ? $payment->rent_amount : $rentAmount,
                'discount_amount' => $payment ? $payment->discount_amount : 0,
                'fine_amount' => $payment ? $payment->fine_amount : 0,
                'cash_paid' => $payment ? $payment->cash_paid_amount : 0,
                'upi_paid' => $payment ? $payment->upi_paid_amount : 0,
                'total_paid' => $currentPaid,
                'balance' => $totalDue,
                'status' => $status,
                'payment_date' => $payment ? $payment->payment_date->format('d M Y') : 'N/A',
                'remark' => $payment ? str_replace(',', ';', $payment->remark ?? '') : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded')
            ];
        }

        $csv = "Receipt,Resident,Hostel,Room,Month,Year,Rent,Discount,Fine,Cash,UPI,Total Paid,Balance,Status,Payment Date,Remark\n";

        foreach ($exportData as $row) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,%s,%s,%s\n",
                $row['receipt_no'],
                $row['resident_name'],
                $row['hostel_name'],
                $row['room_no'],
                $row['month'],
                $row['year'],
                $row['rent_amount'],
                $row['discount_amount'],
                $row['fine_amount'],
                $row['cash_paid'],
                $row['upi_paid'],
                $row['total_paid'],
                $row['balance'],
                $row['status'],
                $row['payment_date'],
                $row['remark']
            );
        }

        $filename = 'payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;
        $filterStatus = $request->status ?? null;
        $search = $request->search ?? null;

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

        if ($search) {
            $residentsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('resident_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $activeResidents = $residentsQuery->orderBy('name')->get();
        $residentIds = $activeResidents->pluck('id')->toArray();

        $paymentsQuery = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $month)
            ->where('year', $year)
            ->whereIn('resident_id', $residentIds);

        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $exportData = [];
        $summary = [
            'total' => 0,
            'total_rent' => 0,
            'total_collected' => 0,
            'total_balance' => 0,
            'paid' => 0,
            'pending' => 0,
            'partial' => 0,
            'unpaid' => 0
        ];

        foreach ($activeResidents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment) {
                if ($payment->status === 'PAID' && $currentBalance == 0) {
                    $status = 'PAID';
                } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                    $status = 'PARTIAL';
                } elseif ($payment->status === 'PENDING') {
                    $status = 'PENDING';
                } else {
                    $status = $payment->status;
                }
            } else {
                $status = 'UNPAID';
            }

            if ($filterStatus && $filterStatus !== $status) {
                continue;
            }

            $exportData[] = [
                'receipt_no' => $payment ? $payment->receipt_no : 'N/A',
                'resident' => $resident,
                'payment' => $payment,
                'rent_amount' => $payment ? $payment->rent_amount : $rentAmount,
                'discount_amount' => $payment ? $payment->discount_amount : 0,
                'fine_amount' => $payment ? $payment->fine_amount : 0,
                'cash_paid' => $payment ? $payment->cash_paid_amount : 0,
                'upi_paid' => $payment ? $payment->upi_paid_amount : 0,
                'total_paid' => $currentPaid,
                'balance' => $totalDue,
                'status' => $status,
                'payment_date' => $payment ? $payment->payment_date : null,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded'),
                'month' => $month,
                'year' => $year,
                'month_name' => date('F', mktime(0,0,0,$month,1)),
                'has_previous_pending' => $previousPending > 0,
                'previous_pending_amount' => $previousPending
            ];

            $summary['total']++;
            $summary['total_rent'] += $rentAmount;
            $summary['total_collected'] += $currentPaid;
            $summary['total_balance'] += $totalDue;
            
            if ($status === 'PAID') $summary['paid']++;
            elseif ($status === 'PENDING') $summary['pending']++;
            elseif ($status === 'PARTIAL') $summary['partial']++;
            elseif ($status === 'UNPAID') $summary['unpaid']++;
        }

        $data = [
            'title' => 'Payment Report',
            'payments' => $exportData,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y H:i'),
            'user' => $user,
            'filters' => [
                'month' => date('F', mktime(0,0,0,$month,1)),
                'year' => $year,
                'status' => $filterStatus ?? 'All',
                'hostel' => $hostelId ? (Hostel::find($hostelId)->hostel_name ?? 'All') : 'All',
                'search' => $search ?? ''
            ]
        ];

        $pdf = PDF::loadView('admin.payments.pdf.all-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payments-' . date('Y-m-d') . '.pdf');
    }

    public function exportSummary(Request $request)
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
            'partial' => 0,
            'unpaid' => 0
        ];

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        foreach ($hostels as $hostel) {
            $residentsQuery = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE');
            $residentsQuery = $this->filterResidentsByMonth($residentsQuery, $month, $year);
            $residents = $residentsQuery->count();

            $payments = Payment::whereHas('resident', function ($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->where('month', $month)->where('year', $year)->get();

            $activeResidents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->get();
            $activeResidents = $this->filterResidentsByMonth($activeResidents, $month, $year)->get();
            $residentIds = $activeResidents->pluck('id')->toArray();
            $paymentsByResident = $payments->keyBy('resident_id');

            $paidCount = 0;
            $pendingCount = 0;
            $partialCount = 0;
            $unpaidCount = 0;
            $totalRent = 0;
            $totalCollected = 0;
            $totalBalance = 0;

            foreach ($activeResidents as $resident) {
                $payment = $paymentsByResident->get($resident->id);
                $previousPending = $this->getPreviousPending($resident->id, $month, $year);
                $rentAmount = (float) ($resident->rent_amount ?? 0);
                
                if ($payment) {
                    $currentBalance = (float) $payment->balance_amount;
                    $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
                } else {
                    $currentBalance = $rentAmount;
                    $currentPaid = 0;
                }
                
                $totalDue = $previousPending + $currentBalance;

                if ($previousPending > 0) {
                    $status = 'PENDING';
                    $pendingCount++;
                } elseif (!$payment && $previousPending == 0) {
                    $status = 'UNPAID';
                    $unpaidCount++;
                } elseif ($payment) {
                    if ($payment->status === 'PAID' && $currentBalance == 0) {
                        $status = 'PAID';
                        $paidCount++;
                    } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                        $status = 'PARTIAL';
                        $partialCount++;
                    } elseif ($payment->status === 'PENDING') {
                        $status = 'PENDING';
                        $pendingCount++;
                    } else {
                        $status = $payment->status;
                    }
                } else {
                    $status = 'UNPAID';
                    $unpaidCount++;
                }

                $totalRent += $rentAmount;
                $totalCollected += $currentPaid;
                $totalBalance += $totalDue;
            }

            $hostelSummaries[] = [
                'hostel' => $hostel,
                'residents' => $residents,
                'payments_count' => $payments->count(),
                'total_rent' => $totalRent,
                'total_collected' => $totalCollected,
                'total_balance' => $totalBalance,
                'paid' => $paidCount,
                'pending' => $pendingCount,
                'partial' => $partialCount,
                'unpaid' => $unpaidCount
            ];

            $grandTotal['residents'] += $residents;
            $grandTotal['payments'] += $payments->count();
            $grandTotal['rent'] += $totalRent;
            $grandTotal['collected'] += $totalCollected;
            $grandTotal['balance'] += $totalBalance;
            $grandTotal['paid'] += $paidCount;
            $grandTotal['pending'] += $pendingCount;
            $grandTotal['partial'] += $partialCount;
            $grandTotal['unpaid'] += $unpaidCount;
        }

        $data = [
            'title' => 'Payment Summary Report',
            'hostelSummaries' => $hostelSummaries,
            'grandTotal' => $grandTotal,
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.payment-summary', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payment-summary-' . date('Y-m-d') . '.pdf');
    }

    public function exportHostelWise(Request $request)
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
        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');

        $residents = Resident::with(['room'])
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $residents = $this->filterResidentsByMonth($residents, $month, $year)->get();

        $paymentsQuery = Payment::with(['resident', 'resident.room'])
            ->whereHas('resident', function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            })
            ->where('month', $month)
            ->where('year', $year);

        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $residentSummaries = [];
        $summary = [
            'total_residents' => $residents->count(),
            'total_payments' => $payments->count(),
            'total_rent' => 0,
            'total_collected' => 0,
            'total_balance' => 0,
            'paid' => 0,
            'pending' => 0,
            'partial' => 0,
            'unpaid' => 0
        ];

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            if ($previousPending > 0) {
                $status = 'PENDING';
                $summary['pending']++;
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
                $summary['unpaid']++;
            } elseif ($payment) {
                if ($payment->status === 'PAID' && $currentBalance == 0) {
                    $status = 'PAID';
                    $summary['paid']++;
                } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                    $status = 'PARTIAL';
                    $summary['partial']++;
                } elseif ($payment->status === 'PENDING') {
                    $status = 'PENDING';
                    $summary['pending']++;
                } else {
                    $status = $payment->status;
                }
            } else {
                $status = 'UNPAID';
                $summary['unpaid']++;
            }

            $summary['total_rent'] += $rentAmount;
            $summary['total_collected'] += $currentPaid;
            $summary['total_balance'] += $totalDue;

            $residentSummaries[] = [
                'resident' => $resident,
                'payments' => $payment ? [$payment] : [],
                'total_paid' => $currentPaid,
                'total_balance' => $totalDue,
                'count' => $payment ? 1 : 0,
                'status' => $status,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded')
            ];
        }

        $data = [
            'title' => 'Hostel Payment Report',
            'hostel' => $hostel,
            'residentSummaries' => $residentSummaries,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user,
            'month' => date('F', mktime(0,0,0,$month,1)),
            'year' => $year
        ];

        $pdf = PDF::loadView('admin.payments.pdf.hostel-wise', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('hostel-' . $hostel->hostel_code . '-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportPaid(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('status', 'PAID');

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        } else {
            $query->where('month', now()->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        } else {
            $query->where('year', now()->year);
        }

        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $summary = [
            'total' => $payments->count(),
            'total_rent' => $payments->sum('rent_amount'),
            'total_collected' => $payments->sum('cash_paid_amount') + $payments->sum('upi_paid_amount')
        ];

        $csv = "Receipt,Resident,Hostel,Room,Month,Year,Rent,Discount,Fine,Cash,UPI,Total Paid,Payment Date,Txn ID,Remark\n";

        foreach ($payments as $payment) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,%s,%s,%s\n",
                $payment->receipt_no,
                $payment->resident->name ?? 'N/A',
                $payment->resident->hostel->hostel_name ?? 'N/A',
                $payment->resident->room->room_no ?? 'N/A',
                date('F', mktime(0,0,0,$payment->month,1)),
                $payment->year,
                $payment->rent_amount,
                $payment->discount_amount ?? 0,
                $payment->fine_amount ?? 0,
                $payment->cash_paid_amount ?? 0,
                $payment->upi_paid_amount ?? 0,
                ($payment->cash_paid_amount + $payment->upi_paid_amount),
                $payment->payment_date->format('d M Y'),
                $payment->transaction_id ?? '',
                str_replace(',', ';', $payment->remark ?? '')
            );
        }

        $filename = 'paid-payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportUnpaidSummary(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

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

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        $paymentsQuery = Payment::where('month', $month)->where('year', $year);
        if ($hostelId) {
            $paymentsQuery->whereHas('resident', function($q) use ($hostelId) {
                $q->where('hostel_id', $hostelId);
            });
        }
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $paymentsQuery->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }
        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $hostelData = [];
        $totalOverall = 0;
        $totalUnpaidCount = 0;

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;
            
            if ($totalDue <= 0) {
                continue;
            }
            
            $hostelName = $resident->hostel->hostel_name ?? 'Unknown Hostel';
            
            if (!isset($hostelData[$hostelName])) {
                $hostelData[$hostelName] = [
                    'hostel_id' => $resident->hostel_id,
                    'residents' => []
                ];
            }
            
            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment && $payment->status === 'PAID' && $currentBalance == 0) {
                $status = 'PAID';
            } elseif ($payment && ($payment->status === 'PARTIAL' || $currentBalance > 0)) {
                $status = 'PARTIAL';
            } elseif ($payment && $payment->status === 'PENDING') {
                $status = 'PENDING';
            } else {
                $status = 'PENDING';
            }
            
            $hostelData[$hostelName]['residents'][] = [
                'name' => $resident->name,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'phone' => $resident->phone ?? '-',
                'rent' => $rentAmount,
                'previous_pending' => $previousPending,
                'current_balance' => $currentBalance,
                'current_paid' => $currentPaid,
                'total_due' => $totalDue,
                'status' => $status,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded'),
                'has_payment' => $payment ? 'Yes' : 'No'
            ];
            
            $totalOverall += $totalDue;
            $totalUnpaidCount++;
        }

        // Build CSV (same as before)
        $csv = "==================================================\n";
        $csv .= "UNPAID PAYMENTS SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Report Month: " . date('F', mktime(0,0,0,$month,1)) . " " . $year . "\n";
        $csv .= "Generated: " . now()->format('d M Y H:i A') . "\n";
        $csv .= "Total Unpaid Residents: " . $totalUnpaidCount . "\n";
        $csv .= "Total Due Amount: ₹" . number_format($totalOverall, 2) . "\n";
        $csv .= "==================================================\n\n";

        foreach ($hostelData as $hostelName => $data) {
            $csv .= "\n🏢 " . strtoupper($hostelName) . "\n";
            $csv .= str_repeat('-', 110) . "\n";
            $csv .= "S.No,Name,Room No,Phone,Rent (₹),Previous Pending (₹),Current Balance (₹),Total Due (₹),Status,Has Payment,Remark\n";
            $csv .= str_repeat('-', 110) . "\n";

            $serialNo = 1;
            foreach ($data['residents'] as $resident) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%s,%s,%s\n",
                    $serialNo,
                    $resident['name'],
                    $resident['room_no'],
                    $resident['phone'],
                    $resident['rent'],
                    $resident['previous_pending'],
                    $resident['current_balance'],
                    $resident['total_due'],
                    $resident['status'],
                    $resident['has_payment'],
                    str_replace(',', ';', $resident['remark'])
                );
                $serialNo++;
            }
            
            $subtotal = collect($data['residents'])->sum('total_due');
            $unpaidCount = collect($data['residents'])->filter(function($r) {
                return $r['status'] == 'UNPAID';
            })->count();
            
            $csv .= str_repeat('-', 110) . "\n";
            $csv .= ",,,,SUBTOTAL,,,₹" . number_format($subtotal, 2) . ",,,\n";
            $csv .= ",,,,Unpaid Residents: " . $unpaidCount . ",,,\n";
            $csv .= "\n";
        }

        $csv .= "==================================================\n";
        $csv .= "GRAND TOTAL DUE: ₹" . number_format($totalOverall, 2) . "\n";
        $csv .= "==================================================\n";

        $filename = 'unpaid-summary-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportUnpaidPdf(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

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

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        $paymentsQuery = Payment::where('month', $month)->where('year', $year);
        if ($hostelId) {
            $paymentsQuery->whereHas('resident', function($q) use ($hostelId) {
                $q->where('hostel_id', $hostelId);
            });
        }
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $paymentsQuery->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }
        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $hostelData = [];
        $totalOverall = 0;
        $totalUnpaidCount = 0;

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;
            
            if ($totalDue <= 0) {
                continue;
            }

            $hostelName = $resident->hostel->hostel_name ?? 'Unknown Hostel';
            
            if (!isset($hostelData[$hostelName])) {
                $hostelData[$hostelName] = [
                    'hostel_id' => $resident->hostel_id,
                    'residents' => []
                ];
            }
            
            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment && $payment->status === 'PAID' && $currentBalance == 0) {
                $status = 'PAID';
            } elseif ($payment && ($payment->status === 'PARTIAL' || $currentBalance > 0)) {
                $status = 'PARTIAL';
            } elseif ($payment && $payment->status === 'PENDING') {
                $status = 'PENDING';
            } else {
                $status = 'PENDING';
            }
            
            $hostelData[$hostelName]['residents'][] = [
                'name' => $resident->name,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'phone' => $resident->phone ?? '-',
                'rent' => $rentAmount,
                'previous_pending' => $previousPending,
                'current_balance' => $currentBalance,
                'current_paid' => $currentPaid,
                'total_due' => $totalDue,
                'status' => $status,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded'),
                'has_payment' => $payment ? 'Yes' : 'No'
            ];
            
            $totalOverall += $totalDue;
            $totalUnpaidCount++;
        }

        $data = [
            'hostelData' => $hostelData,
            'month' => date('F', mktime(0,0,0,$month,1)),
            'year' => $year,
            'totalOverall' => $totalOverall,
            'totalResidents' => $totalUnpaidCount,
            'generated_at' => now()->format('d M Y H:i A'),
            'filters' => [
                'hostel' => $hostelId ? (Hostel::find($hostelId)->hostel_name ?? 'All') : 'All'
            ],
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.unpaid-summary', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('unpaid-summary-' . date('Y-m-d') . '.pdf');
    }

    public function exportPaymentStatus(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

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

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        $paymentsQuery = Payment::where('month', $month)->where('year', $year);
        if ($hostelId) {
            $paymentsQuery->whereHas('resident', function($q) use ($hostelId) {
                $q->where('hostel_id', $hostelId);
            });
        }
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $paymentsQuery->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }
        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $pendingData = [];
        $partialData = [];
        $unpaidData = [];
        $paidData = [];

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            $residentData = [
                'name' => $resident->name,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'phone' => $resident->phone ?? '-',
                'rent' => $rentAmount,
                'previous_pending' => $previousPending,
                'current_balance' => $currentBalance,
                'current_paid' => $currentPaid,
                'total_due' => $totalDue,
                'hostel_name' => $resident->hostel->hostel_name ?? 'Unknown Hostel'
            ];

            if ($previousPending > 0) {
                $residentData['status'] = 'PENDING';
                $residentData['remark'] = $payment ? $payment->remark : 'Previous months pending';
                $pendingData[] = $residentData;
            } elseif (!$payment && $previousPending == 0) {
                $residentData['status'] = 'UNPAID';
                $residentData['remark'] = 'No payment recorded for this month';
                $unpaidData[] = $residentData;
            } elseif ($payment && $payment->status === 'PAID' && $currentBalance == 0) {
                $residentData['status'] = 'PAID';
                $residentData['remark'] = $payment->remark ?? 'Fully paid';
                $paidData[] = $residentData;
            } elseif ($payment && ($payment->status === 'PARTIAL' || $currentBalance > 0)) {
                $residentData['status'] = 'PARTIAL';
                $residentData['remark'] = $payment->remark ?? 'Partial payment';
                $partialData[] = $residentData;
            } elseif ($payment && $payment->status === 'PENDING') {
                $residentData['status'] = 'PENDING';
                $residentData['remark'] = $payment->remark ?? 'Payment pending';
                $pendingData[] = $residentData;
            } else {
                $residentData['status'] = 'PENDING';
                $residentData['remark'] = 'Pending payment';
                $pendingData[] = $residentData;
            }
        }

        // Build CSV (short version - same as before)
        $csv = "==================================================\n";
        $csv .= "PAYMENT STATUS SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Report Month: " . date('F', mktime(0,0,0,$month,1)) . " " . $year . "\n";
        $csv .= "Generated: " . now()->format('d M Y H:i A') . "\n";
        $csv .= "==================================================\n\n";

        // PENDING SECTION
        $csv .= "🔴 PENDING PAYMENTS (" . count($pendingData) . ")\n";
        $csv .= "Total Pending Residents: " . count($pendingData) . "\n";
        $csv .= str_repeat('=', 80) . "\n";
        $csv .= "S.No,Name,Room No,Phone,Rent (₹),Previous Pending (₹),Current Balance (₹),Total Due (₹),Status,Remark\n";
        $csv .= str_repeat('-', 80) . "\n";

        $serialNo = 1;
        foreach ($pendingData as $resident) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%s,%s\n",
                $serialNo,
                $resident['name'],
                $resident['room_no'],
                $resident['phone'],
                $resident['rent'],
                $resident['previous_pending'],
                $resident['current_balance'],
                $resident['total_due'],
                $resident['status'],
                str_replace(',', ';', $resident['remark'])
            );
            $serialNo++;
        }
        $csv .= "\n";

        // PARTIAL SECTION
        $csv .= "🟡 PARTIAL PAYMENTS (" . count($partialData) . ")\n";
        $csv .= "Total Partial Residents: " . count($partialData) . "\n";
        $csv .= str_repeat('=', 80) . "\n";
        $csv .= "S.No,Name,Room No,Phone,Rent (₹),Paid (₹),Balance (₹),Status,Remark\n";
        $csv .= str_repeat('-', 80) . "\n";

        $serialNo = 1;
        foreach ($partialData as $resident) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%.2f,%.2f,%s,%s\n",
                $serialNo,
                $resident['name'],
                $resident['room_no'],
                $resident['phone'],
                $resident['rent'],
                $resident['current_paid'],
                $resident['total_due'],
                $resident['status'],
                str_replace(',', ';', $resident['remark'])
            );
            $serialNo++;
        }
        $csv .= "\n";

        // UNPAID SECTION
        $csv .= "⬜ UNPAID PAYMENTS (" . count($unpaidData) . ")\n";
        $csv .= "Total Unpaid Residents: " . count($unpaidData) . "\n";
        $csv .= str_repeat('=', 80) . "\n";
        $csv .= "S.No,Name,Room No,Phone,Rent (₹),Status,Remark\n";
        $csv .= str_repeat('-', 80) . "\n";

        $serialNo = 1;
        foreach ($unpaidData as $resident) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%s,%s\n",
                $serialNo,
                $resident['name'],
                $resident['room_no'],
                $resident['phone'],
                $resident['rent'],
                $resident['status'],
                str_replace(',', ';', $resident['remark'])
            );
            $serialNo++;
        }
        $csv .= "\n";

        // PAID SECTION
        $csv .= "✅ PAID PAYMENTS (" . count($paidData) . ")\n";
        $csv .= "Total Paid Residents: " . count($paidData) . "\n";
        $csv .= str_repeat('=', 80) . "\n";
        $csv .= "S.No,Name,Room No,Phone,Rent (₹),Paid (₹),Status,Remark\n";
        $csv .= str_repeat('-', 80) . "\n";

        $serialNo = 1;
        foreach ($paidData as $resident) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%.2f,%s,%s\n",
                $serialNo,
                $resident['name'],
                $resident['room_no'],
                $resident['phone'],
                $resident['rent'],
                $resident['current_paid'],
                $resident['status'],
                str_replace(',', ';', $resident['remark'])
            );
            $serialNo++;
        }
        $csv .= "\n";

        $csv .= "==================================================\n";
        $csv .= "SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Total Pending: " . count($pendingData) . " residents\n";
        $csv .= "Total Partial: " . count($partialData) . " residents\n";
        $csv .= "Total Unpaid: " . count($unpaidData) . " residents\n";
        $csv .= "Total Paid: " . count($paidData) . " residents\n";
        $csv .= "==================================================\n";

        $filename = 'payment-status-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportPaymentStatusPdf(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

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

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        $paymentsQuery = Payment::where('month', $month)->where('year', $year);
        if ($hostelId) {
            $paymentsQuery->whereHas('resident', function($q) use ($hostelId) {
                $q->where('hostel_id', $hostelId);
            });
        }
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $paymentsQuery->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }
        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $pendingData = [];
        $partialData = [];
        $unpaidData = [];
        $paidData = [];

        foreach ($residents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            $residentData = [
                'name' => $resident->name,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'phone' => $resident->phone ?? '-',
                'rent' => $rentAmount,
                'previous_pending' => $previousPending,
                'current_balance' => $currentBalance,
                'current_paid' => $currentPaid,
                'total_due' => $totalDue,
                'hostel_name' => $resident->hostel->hostel_name ?? 'Unknown Hostel'
            ];

            if ($previousPending > 0) {
                $residentData['status'] = 'PENDING';
                $residentData['remark'] = $payment ? $payment->remark : 'Previous months pending';
                $pendingData[] = $residentData;
            } elseif (!$payment && $previousPending == 0) {
                $residentData['status'] = 'UNPAID';
                $residentData['remark'] = 'No payment recorded for this month';
                $unpaidData[] = $residentData;
            } elseif ($payment && $payment->status === 'PAID' && $currentBalance == 0) {
                $residentData['status'] = 'PAID';
                $residentData['remark'] = $payment->remark ?? 'Fully paid';
                $paidData[] = $residentData;
            } elseif ($payment && ($payment->status === 'PARTIAL' || $currentBalance > 0)) {
                $residentData['status'] = 'PARTIAL';
                $residentData['remark'] = $payment->remark ?? 'Partial payment';
                $partialData[] = $residentData;
            } elseif ($payment && $payment->status === 'PENDING') {
                $residentData['status'] = 'PENDING';
                $residentData['remark'] = $payment->remark ?? 'Payment pending';
                $pendingData[] = $residentData;
            } else {
                $residentData['status'] = 'PENDING';
                $residentData['remark'] = 'Pending payment';
                $pendingData[] = $residentData;
            }
        }

        $data = [
            'pendingData' => $pendingData,
            'partialData' => $partialData,
            'unpaidData' => $unpaidData,
            'paidData' => $paidData,
            'month' => date('F', mktime(0,0,0,$month,1)),
            'year' => $year,
            'generated_at' => now()->format('d M Y H:i A'),
            'filters' => [
                'hostel' => $hostelId ? (Hostel::find($hostelId)->hostel_name ?? 'All') : 'All'
            ],
            'user' => $user,
            'totalResidents' => count($residents),
            'totalPending' => count($pendingData),
            'totalPartial' => count($partialData),
            'totalUnpaid' => count($unpaidData),
            'totalPaid' => count($paidData),
        ];

        $pdf = PDF::loadView('admin.payments.pdf.payment-status', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payment-status-' . date('Y-m-d') . '.pdf');
    }

    // ============================================================
    // PDF VIEW METHODS
    // ============================================================

    public function pdfAllPayments(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;
        $filterStatus = $request->status ?? null;
        $search = $request->search ?? null;

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

        if ($search) {
            $residentsQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('resident_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $activeResidents = $residentsQuery->orderBy('name')->get();
        $residentIds = $activeResidents->pluck('id')->toArray();

        $paymentsQuery = Payment::with(['resident', 'resident.hostel', 'resident.room'])
            ->where('month', $month)
            ->where('year', $year)
            ->whereIn('resident_id', $residentIds);

        $payments = $paymentsQuery->get()->keyBy('resident_id');

        $exportData = [];
        $summary = [
            'total' => 0,
            'total_rent' => 0,
            'total_collected' => 0,
            'total_balance' => 0,
            'paid' => 0,
            'pending' => 0,
            'partial' => 0,
            'unpaid' => 0
        ];

        foreach ($activeResidents as $resident) {
            $payment = $payments->get($resident->id);
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $rentAmount = (float) ($resident->rent_amount ?? 0);
            
            if ($payment) {
                $currentBalance = (float) $payment->balance_amount;
                $currentPaid = (float) ($payment->cash_paid_amount + $payment->upi_paid_amount);
            } else {
                $currentBalance = $rentAmount;
                $currentPaid = 0;
            }
            
            $totalDue = $previousPending + $currentBalance;

            if ($previousPending > 0) {
                $status = 'PENDING';
            } elseif (!$payment && $previousPending == 0) {
                $status = 'UNPAID';
            } elseif ($payment) {
                if ($payment->status === 'PAID' && $currentBalance == 0) {
                    $status = 'PAID';
                } elseif ($payment->status === 'PARTIAL' || $currentBalance > 0) {
                    $status = 'PARTIAL';
                } elseif ($payment->status === 'PENDING') {
                    $status = 'PENDING';
                } else {
                    $status = $payment->status;
                }
            } else {
                $status = 'UNPAID';
            }

            if ($filterStatus && $filterStatus !== $status) {
                continue;
            }

            $exportData[] = [
                'receipt_no' => $payment ? $payment->receipt_no : 'N/A',
                'resident' => $resident,
                'payment' => $payment,
                'rent_amount' => $payment ? $payment->rent_amount : $rentAmount,
                'discount_amount' => $payment ? $payment->discount_amount : 0,
                'fine_amount' => $payment ? $payment->fine_amount : 0,
                'cash_paid' => $payment ? $payment->cash_paid_amount : 0,
                'upi_paid' => $payment ? $payment->upi_paid_amount : 0,
                'total_paid' => $currentPaid,
                'balance' => $totalDue,
                'status' => $status,
                'payment_date' => $payment ? $payment->payment_date : null,
                'remark' => $payment ? $payment->remark : ($previousPending > 0 ? 'Previous months pending' : 'No payment recorded'),
                'month' => $month,
                'year' => $year,
                'month_name' => date('F', mktime(0,0,0,$month,1)),
                'has_previous_pending' => $previousPending > 0,
                'previous_pending_amount' => $previousPending
            ];

            $summary['total']++;
            $summary['total_rent'] += $rentAmount;
            $summary['total_collected'] += $currentPaid;
            $summary['total_balance'] += $totalDue;
            
            if ($status === 'PAID') $summary['paid']++;
            elseif ($status === 'PENDING') $summary['pending']++;
            elseif ($status === 'PARTIAL') $summary['partial']++;
            elseif ($status === 'UNPAID') $summary['unpaid']++;
        }

        $data = [
            'title' => 'All Payments Report',
            'payments' => $exportData,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user,
            'filters' => [
                'month' => date('F', mktime(0,0,0,$month,1)),
                'year' => $year,
                'status' => $filterStatus ?? 'All',
                'hostel' => $hostelId ? (Hostel::find($hostelId)->hostel_name ?? 'All') : 'All',
                'search' => $search ?? ''
            ]
        ];

        $pdf = PDF::loadView('admin.payments.pdf.all-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('all-payments-' . date('Y-m-d') . '.pdf');
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
            'generated_at' => now()->format('d M Y H:i A'),
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
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.bulk-receipts', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('bulk-receipts-' . date('Y-m-d') . '.pdf');
    }
}