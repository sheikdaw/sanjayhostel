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
     * Get previous pending total
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

        // Get hostels based on user role
        if ($user->role === 'admin') {
            $hostels = Hostel::where('status', 'ACTIVE')->get();
        } else {
            $hostelIds = $user->hostel_ids ?? [];
            $hostels = Hostel::whereIn('id', $hostelIds)->where('status', 'ACTIVE')->get();
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
        $filterMonth = $request->month ?? now()->month;
        $filterYear = $request->year ?? now()->year;
        $filterHostelId = $request->hostel_id ?? null;
        $filterStatus = $request->status ?? null;
        $search = $request->search ?? null;

        // BUILD MAIN QUERY WITH FILTERS
        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

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

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('receipt_no', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('resident', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('resident_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Apply user role restriction
        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // CALCULATE STATISTICS
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

        $pendingPayments = $payments->where('status', 'PENDING');

        // Get filter labels
        $filterMonthName = date('F', mktime(0, 0, 0, $filterMonth, 1));
        $filterHostelName = $filterHostelId ? (Hostel::find($filterHostelId)->hostel_name ?? 'All Hostels') : 'All Hostels';

        // Get rooms for filter
        $rooms = Room::where('status', 'ACTIVE')->get();

        return view('admin.payments.index', compact(
            'payments',
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
            'search'
        ));
    }

    /**
     * Store a newly created payment with discount logic
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

        // Permission check
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
            $totalPaid = (float) $request->cash_paid_amount + (float) $request->upi_paid_amount + $fineAmount;

            // Get previous pending
            $previousPendingList = $this->getPreviousPendingDetails($resident->id, $month, $year);
            $totalPreviousPending = $previousPendingList->sum('balance_amount');

            // Check if already paid
            $existingPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingPayment && $existingPayment->status === 'PAID') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ Already paid for " . date('F Y', mktime(0,0,0,$month,1,$year))
                ], 422);
            }

            // ✅ DISCOUNT LOGIC: Only apply if customer pays enough
            $tentativeDiscount = (float) $this->calculateDiscount($paymentDate);
            $totalNeedToPayWithDiscount = $rentAmount + $totalPreviousPending - $tentativeDiscount;

            if ($totalNeedToPayWithDiscount <= $totalPaid) {
                $discount = $tentativeDiscount;
                $discountApplied = true;
                $discountReason = "✅ Discount applied: ₹" . number_format($discount, 2);
            } else {
                $discount = 0;
                $discountApplied = false;
                $discountReason = "❌ No discount: Need ₹" . number_format($totalNeedToPayWithDiscount, 2) . ", paid ₹" . number_format($totalPaid, 2);
            }

            $totalNeedToPay = $rentAmount + $totalPreviousPending - $discount;
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
            $currentDue = $rentAmount - $discount + $fineAmount;
            $currentPaid = min($remaining, $currentDue);
            $remaining -= $currentPaid;
            $currentBalance = max(0, $currentDue - $currentPaid);

            // Step 3: Advance
            $advanceAmount = max(0, $remaining);
            $totalBalance = $previousBalance + $currentBalance;

            $status = ($totalBalance <= 0) ? 'PAID' : (($totalPaid > 0) ? 'PARTIAL' : 'PENDING');

            // Build remark
            $monthName = date('F Y', mktime(0,0,0,$month,1,$year));
            $remark = $discountReason . " | ";
            $remark .= $previousPaid > 0 ? "✅ Previous cleared ₹" . number_format($previousPaid, 2) . " | " : "";
            $remark .= $currentPaid > 0 ? ($currentBalance <= 0 ? "✅ {$monthName} paid ₹" . number_format($currentPaid, 2) : "🟡 {$monthName} partial ₹" . number_format($currentPaid, 2)) : "⏳ {$monthName} not paid";
            $remark .= $advanceAmount > 0 ? " | 💰 Advance ₹" . number_format($advanceAmount, 2) : "";
            $remark .= $totalBalance > 0 ? " | 📊 Pending ₹" . number_format($totalBalance, 2) : " | ✅ All cleared!";

            // Create or update payment
            if ($existingPayment) {
                $existingPayment->cash_paid_amount += $currentPaid;
                $existingPayment->balance_amount = $currentBalance;
                $existingPayment->status = $status;
                $existingPayment->payment_date = $paymentDate;
                $existingPayment->discount_amount = $discount;
                $existingPayment->fine_amount = $fineAmount;
                $existingPayment->remark = $remark;
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
                    'cash_paid_amount' => $currentPaid,
                    'upi_paid_amount' => 0,
                    'balance_amount' => $currentBalance,
                    'payment_date' => $paymentDate,
                    'transaction_id' => $request->transaction_id,
                    'status' => $status,
                    'payment_type' => 'all',
                    'previous_pending_cleared' => $previousPaid,
                    'remark' => $remark,
                ]);
            }

            DB::commit();

            $message = $this->buildDetailedResponseMessage(
                $totalPaid, $previousPaid, $currentPaid, $advanceAmount,
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
                        'discount' => $discount,
                        'previous_pending' => $totalPreviousPending,
                        'total_need_to_pay' => $totalNeedToPay,
                        'customer_paid' => $toPay,
                        'scenario' => $totalNeedToPay > $toPay ? 'Partial' : ($totalNeedToPay == $toPay ? 'Exact' : 'Overpayment')
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
     * Edit payment
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
     * Update payment
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
            'rent_amount' => $request->rent_amount,
            'discount_amount' => $request->discount_amount ?? 0,
            'fine_amount' => $request->fine_amount ?? 0,
            'cash_paid_amount' => $request->cash_paid_amount,
            'upi_paid_amount' => $request->upi_paid_amount,
            'balance_amount' => $balanceAmount,
            'payment_date' => $request->payment_date,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated!',
            'data' => $payment
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
     * Mark as paid
     */
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

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid!'
        ]);
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

        if ($payment && $payment->status === 'PAID') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => $payment->status,
                'receipt_no' => $payment->receipt_no,
                'amount' => $payment->rent_amount
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
            $totalPaid = $request->total_paid ?? 0;

            $tentativeDiscount = $this->calculateDiscount($paymentDate);
            $totalPreviousPending = $this->getPreviousPending($resident->id, $month, $year);

            $totalNeedToPayWithDiscount = $resident->rent_amount + $totalPreviousPending - $tentativeDiscount;

            if ($totalNeedToPayWithDiscount <= $totalPaid) {
                $discount = $tentativeDiscount;
                $discountApplied = true;
            } else {
                $discount = 0;
                $discountApplied = false;
            }

            $currentDue = $resident->rent_amount - $discount;
            $remaining = $totalPaid;
            $previousPaid = min($remaining, $totalPreviousPending);
            $remaining -= $previousPaid;
            $currentPaid = min($remaining, $currentDue);
            $remaining -= $currentPaid;
            $advanceAmount = max(0, $remaining);

            $previewRemark = ($discountApplied ? "✅ Discount ₹" . number_format($discount, 2) : "❌ No discount") . " | ";
            $previewRemark .= $previousPaid > 0 ? "Previous: ₹" . number_format($previousPaid, 2) . " | " : "";
            $previewRemark .= $currentPaid > 0 ? "Current: ₹" . number_format($currentPaid, 2) : "Current: ₹0";
            $previewRemark .= $advanceAmount > 0 ? " | Advance: ₹" . number_format($advanceAmount, 2) : "";

            return response()->json([
                'success' => true,
                'data' => [
                    'rent' => $resident->rent_amount,
                    'discount' => $discount,
                    'previous_pending' => $totalPreviousPending,
                    'current_due' => $currentDue,
                    'total_due' => $currentDue + $totalPreviousPending,
                    'total_paid' => $totalPaid,
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

            // Check previous pending - if exists, skip
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

            Payment::create([
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
    // EXPORT METHODS
    // ============================================================

    /**
     * Export filtered payments as CSV
     */
    public function exportFiltered(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

        // Apply ALL filters
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_no', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('resident', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('resident_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        // Build CSV
        $csv = "Receipt,Resident,Hostel,Room,Month,Year,Rent,Discount,Fine,Cash,UPI,Total Paid,Balance,Status,Payment Date,Remark\n";

        foreach ($payments as $payment) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,%s,%s,%s\n",
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
                $payment->balance_amount,
                $payment->status,
                $payment->payment_date->format('d M Y'),
                str_replace(',', ';', $payment->remark ?? '')
            );
        }

        $filename = 'payments-' . date('Y-m-d') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export as PDF
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

        // Apply ALL filters
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_no', 'LIKE', "%{$search}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$search}%")
                  ->orWhereHas('resident', function($sub) use ($search) {
                      $sub->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('resident_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

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
            'title' => 'Payment Report',
            'payments' => $payments,
            'summary' => $summary,
            'generated_at' => now()->format('d M Y H:i'),
            'user' => $user,
            'filters' => [
                'month' => $request->month ?? now()->month,
                'year' => $request->year ?? now()->year,
                'status' => $request->status ?? 'All',
                'hostel' => $request->hostel_id ? (Hostel::find($request->hostel_id)->hostel_name ?? 'All') : 'All',
                'search' => $request->search ?? ''
            ]
        ];

        $pdf = PDF::loadView('admin.payments.pdf.all-payments', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payments-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Payment Summary PDF
     */
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
            'partial' => 0
        ];

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        foreach ($hostels as $hostel) {
            $residents = Resident::where('hostel_id', $hostel->id)
                ->where('status', 'ACTIVE')
                ->count();

            $payments = Payment::whereHas('resident', function ($q) use ($hostel) {
                $q->where('hostel_id', $hostel->id);
            })->where('month', $month)->where('year', $year)->get();

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
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.payment-summary', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('payment-summary-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Hostel Wise Report
     */
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

        $residents = Resident::with(['room'])
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $query = Payment::with(['resident', 'resident.room'])
            ->whereHas('resident', function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });

        // Apply month/year filters
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
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user
        ];

        $pdf = PDF::loadView('admin.payments.pdf.hostel-wise', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('hostel-' . $hostel->hostel_code . '-report-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Paid Payments
     */
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

        // Build CSV
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

    /**
     * Export Unpaid Summary (CSV)
     */
    public function exportUnpaidSummary(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

        // Get residents with unpaid payments
        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        // Build data grouped by hostel
        $hostelData = [];
        $totalOverall = 0;
        $totalUnpaidResidents = 0;

        foreach ($residents as $resident) {
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : 0;
            
            // Check if resident has any payment due
            $hasDue = $previousPending > 0 || $currentBalance > 0;
            
            if ($hasDue) {
                $hostelName = $resident->hostel->hostel_name ?? 'Unknown Hostel';
                
                if (!isset($hostelData[$hostelName])) {
                    $hostelData[$hostelName] = [
                        'hostel_id' => $resident->hostel_id,
                        'residents' => []
                    ];
                }
                
                $hostelData[$hostelName]['residents'][] = [
                    'name' => $resident->name,
                    'room_no' => $resident->room->room_no ?? 'N/A',
                    'phone' => $resident->phone ?? '-',
                    'rent' => (float) ($resident->rent_amount ?? 0),
                    'previous_pending' => $previousPending,
                    'current_balance' => $currentBalance,
                    'total_due' => $previousPending + $currentBalance,
                    'remark' => $currentPayment ? $currentPayment->remark : 'No payment record'
                ];
                
                $totalOverall += ($previousPending + $currentBalance);
                $totalUnpaidResidents++;
            }
        }

        // Build CSV
        $csv = "==================================================\n";
        $csv .= "UNPAID PAYMENTS SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Report Month: " . date('F', mktime(0,0,0,$month,1)) . " " . $year . "\n";
        $csv .= "Generated: " . now()->format('d M Y H:i A') . "\n";
        $csv .= "Total Unpaid Residents: " . $totalUnpaidResidents . "\n";
        $csv .= "Total Due Amount: ₹" . number_format($totalOverall, 2) . "\n";
        $csv .= "==================================================\n\n";

        foreach ($hostelData as $hostelName => $data) {
            $csv .= "\n🏢 " . strtoupper($hostelName) . "\n";
            $csv .= str_repeat('-', 85) . "\n";
            $csv .= "S.No,Name,Room No,Phone,Rent (₹),Previous Pending (₹),Current Balance (₹),Total Due (₹),Remark\n";
            $csv .= str_repeat('-', 85) . "\n";

            $serialNo = 1;
            foreach ($data['residents'] as $resident) {
                $csv .= sprintf(
                    "%d,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%s\n",
                    $serialNo,
                    $resident['name'],
                    $resident['room_no'],
                    $resident['phone'],
                    $resident['rent'],
                    $resident['previous_pending'],
                    $resident['current_balance'],
                    $resident['total_due'],
                    str_replace(',', ';', $resident['remark'])
                );
                $serialNo++;
            }
            
            $subtotal = collect($data['residents'])->sum('total_due');
            $csv .= str_repeat('-', 85) . "\n";
            $csv .= ",,,,SUBTOTAL,,,₹" . number_format($subtotal, 2) . ",\n";
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

    /**
     * Export Unpaid Summary (PDF)
     */
    public function exportUnpaidPdf(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

        // Get residents with unpaid payments
        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        // Build data grouped by hostel
        $hostelData = [];
        $totalOverall = 0;
        $totalUnpaidResidents = 0;

        foreach ($residents as $resident) {
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : 0;
            
            $hasDue = $previousPending > 0 || $currentBalance > 0;
            
            if ($hasDue) {
                $hostelName = $resident->hostel->hostel_name ?? 'Unknown Hostel';
                
                if (!isset($hostelData[$hostelName])) {
                    $hostelData[$hostelName] = [
                        'hostel_id' => $resident->hostel_id,
                        'residents' => []
                    ];
                }
                
                $hostelData[$hostelName]['residents'][] = [
                    'name' => $resident->name,
                    'room_no' => $resident->room->room_no ?? 'N/A',
                    'phone' => $resident->phone ?? '-',
                    'rent' => (float) ($resident->rent_amount ?? 0),
                    'previous_pending' => $previousPending,
                    'current_balance' => $currentBalance,
                    'total_due' => $previousPending + $currentBalance,
                    'remark' => $currentPayment ? $currentPayment->remark : 'No payment record'
                ];
                
                $totalOverall += ($previousPending + $currentBalance);
                $totalUnpaidResidents++;
            }
        }

        $data = [
            'hostelData' => $hostelData,
            'month' => date('F', mktime(0,0,0,$month,1)),
            'year' => $year,
            'totalOverall' => $totalOverall,
            'totalResidents' => $totalUnpaidResidents,
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

    /**
     * Export Payment Status (CSV)
     */
    public function exportPaymentStatus(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

        // Get residents with payments
        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        // Categorize residents
        $pendingData = [];
        $partialData = [];
        $unpaidData = [];
        $paidData = [];

        foreach ($residents as $resident) {
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : 0;
            $currentPaid = $currentPayment ? (float) ($currentPayment->cash_paid_amount + $currentPayment->upi_paid_amount) : 0;
            $totalDue = $previousPending + $currentBalance;

            $residentData = [
                'name' => $resident->name,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'phone' => $resident->phone ?? '-',
                'rent' => (float) ($resident->rent_amount ?? 0),
                'previous_pending' => $previousPending,
                'current_balance' => $currentBalance,
                'current_paid' => $currentPaid,
                'total_due' => $totalDue,
                'status' => $currentPayment ? $currentPayment->status : 'NO PAYMENT',
                'remark' => $currentPayment ? $currentPayment->remark : 'No payment record',
                'hostel_name' => $resident->hostel->hostel_name ?? 'Unknown Hostel'
            ];

            // Categorize
            if ($totalDue > 0 && $currentPaid > 0 && $currentBalance > 0) {
                $partialData[] = $residentData;
            } elseif ($totalDue > 0 && $currentPaid == 0) {
                $unpaidData[] = $residentData;
            } elseif ($totalDue == 0) {
                $paidData[] = $residentData;
            }

            if ($totalDue > 0) {
                $pendingData[] = $residentData;
            }
        }

        // Build CSV
        $csv = "==================================================\n";
        $csv .= "PAYMENT STATUS SUMMARY\n";
        $csv .= "==================================================\n";
        $csv .= "Report Month: " . date('F', mktime(0,0,0,$month,1)) . " " . $year . "\n";
        $csv .= "Generated: " . now()->format('d M Y H:i A') . "\n";
        $csv .= "==================================================\n\n";

        // PENDING SECTION
        $csv .= "🔴 PENDING PAYMENTS (Previous Balance + Current Balance)\n";
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
        $csv .= "🟡 PARTIAL PAYMENTS (Paid some, balance remains)\n";
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
        $csv .= "⬜ UNPAID PAYMENTS (No payment at all)\n";
        $csv .= "Total Unpaid Residents: " . count($unpaidData) . "\n";
        $csv .= str_repeat('=', 80) . "\n";
        $csv .= "S.No,Name,Room No,Phone,Rent (₹),Previous Pending (₹),Total Due (₹),Remark\n";
        $csv .= str_repeat('-', 80) . "\n";

        $serialNo = 1;
        foreach ($unpaidData as $resident) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%.2f,%.2f,%s\n",
                $serialNo,
                $resident['name'],
                $resident['room_no'],
                $resident['phone'],
                $resident['rent'],
                $resident['previous_pending'],
                $resident['total_due'],
                str_replace(',', ';', $resident['remark'])
            );
            $serialNo++;
        }
        $csv .= "\n";

        // PAID SECTION
        $csv .= "✅ PAID PAYMENTS (Fully paid - balance 0)\n";
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

        // SUMMARY
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

    /**
     * Export Payment Status (PDF)
     */
    public function exportPaymentStatusPdf(Request $request)
    {
        $user = auth()->user();

        $month = $request->filled('month') ? (int) $request->month : (int) date('n');
        $year = $request->filled('year') ? (int) $request->year : (int) date('Y');
        $hostelId = $request->filled('hostel_id') ? (int) $request->hostel_id : null;

        // Get residents with payments
        $residentsQuery = Resident::with(['hostel', 'room'])
            ->where('status', 'ACTIVE');

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $residentsQuery->whereIn('hostel_id', $hostelIds);
        }

        if ($hostelId) {
            $residentsQuery->where('hostel_id', $hostelId);
        }

        $residents = $residentsQuery->orderBy('hostel_id')->orderBy('name')->get();

        // Categorize residents
        $pendingData = [];
        $partialData = [];
        $unpaidData = [];
        $paidData = [];

        foreach ($residents as $resident) {
            $previousPending = $this->getPreviousPending($resident->id, $month, $year);
            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : 0;
            $currentPaid = $currentPayment ? (float) ($currentPayment->cash_paid_amount + $currentPayment->upi_paid_amount) : 0;
            $totalDue = $previousPending + $currentBalance;

            $residentData = [
                'name' => $resident->name,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'phone' => $resident->phone ?? '-',
                'rent' => (float) ($resident->rent_amount ?? 0),
                'previous_pending' => $previousPending,
                'current_balance' => $currentBalance,
                'current_paid' => $currentPaid,
                'total_due' => $totalDue,
                'status' => $currentPayment ? $currentPayment->status : 'NO PAYMENT',
                'remark' => $currentPayment ? $currentPayment->remark : 'No payment record',
                'hostel_name' => $resident->hostel->hostel_name ?? 'Unknown Hostel'
            ];

            if ($totalDue > 0 && $currentPaid > 0 && $currentBalance > 0) {
                $partialData[] = $residentData;
            } elseif ($totalDue > 0 && $currentPaid == 0) {
                $unpaidData[] = $residentData;
            } elseif ($totalDue == 0) {
                $paidData[] = $residentData;
            }

            if ($totalDue > 0) {
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
            'user' => $user
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

        $query = Payment::with(['resident', 'resident.hostel', 'resident.room']);

        if ($user->role !== 'admin') {
            $hostelIds = $user->hostel_ids ?? [];
            $query->whereHas('resident', function ($q) use ($hostelIds) {
                $q->whereIn('hostel_id', $hostelIds);
            });
        }

        // Apply filters
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('hostel_id')) {
            $query->whereHas('resident', function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id);
            });
        }

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
            'generated_at' => now()->format('d M Y H:i A'),
            'user' => $user,
            'filters' => $request->all()
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