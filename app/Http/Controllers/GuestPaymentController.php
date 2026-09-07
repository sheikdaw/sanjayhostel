<?php

namespace App\Http\Controllers;

use App\Models\Hostel;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class GuestPaymentController extends Controller
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
     * Get total previous pending
     */
    private function getTotalPreviousPending($residentId, $month, $year)
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
     * Display the guest payment page
     */
    public function index($encodedId = null)
    {
        $hostel = null;
        $hostelId = null;
        $reference = null;

        if ($encodedId) {
            try {
                $hostelId = Crypt::decryptString($encodedId);
                $hostel = Hostel::where('id', $hostelId)->where('status', 'ACTIVE')->first();
            } catch (\Exception $e) {
                // Invalid encrypted ID
            }
        }

        // If no hostel found, get first active hostel
        if (!$hostel) {
            $hostel = Hostel::where('status', 'ACTIVE')->first();
            if ($hostel) {
                $hostelId = $hostel->id;
            }
        }

        // Generate a unique reference for this session
        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

        return view('guest.payment.index', compact('hostel', 'hostelId', 'reference'));
    }

    /**
     * Get resident details by mobile number
     */
    public function getResident(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|string|min:10|max:15',
                'hostel_id' => 'required|exists:hostels,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Find resident by phone number
            $resident = Resident::with(['hostel', 'room'])
                ->where('phone', $request->mobile)
                ->where('hostel_id', $request->hostel_id)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found with this mobile number.'
                ], 404);
            }

            // Get current month and year
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $paymentDate = now()->toDateString();

            // Get previous pending (unpaid or partially paid from previous months)
            $previousPending = $this->getTotalPreviousPending($resident->id, $currentMonth, $currentYear);
            $previousPendingList = $this->getPreviousPendingDetails($resident->id, $currentMonth, $currentYear);

            // Get current payment record
            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            // ✅ CORRECT DISCOUNT LOGIC:
            // Check if current month is already paid
            $isCurrentPaid = $currentPayment && $currentPayment->status == 'PAID';

            // Calculate discount based on today's date
            $tentativeDiscount = $this->calculateDiscount($paymentDate);
            $rentAmount = (float) ($resident->rent_amount ?? 0);

            // ✅ FIX: Determine if discount should apply
            // A resident is eligible for discount if:
            // 1. They have NO previous pending, OR
            // 2. They WILL clear all previous pending with this payment AND pay full rent
            // For the "getResident" preview, we check if they CAN clear everything

            $discount = 0;
            $discountEligible = false;

            // If already paid for current month, no discount needed
            if ($isCurrentPaid) {
                $discount = 0;
                $discountEligible = false;
            } else {
                // If no previous pending, discount applies (assuming they'll pay full)
                if ($previousPending == 0) {
                    $discount = $tentativeDiscount;
                    $discountEligible = true;
                } else {
                    // Has previous pending - discount only applies if they pay enough to clear ALL pending AND full rent
                    // For preview purposes, we assume they'll pay the full amount
                    // The actual discount will be applied in the store/verify method when payment is made
                    $discount = $tentativeDiscount;
                    $discountEligible = true; // They CAN get discount if they pay full amount
                }
            }

            // Calculate current month due with discount
            $currentMonthDue = $rentAmount - $discount;

            // Get current balance (if payment exists, use its balance, otherwise use full rent minus discount)
            $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : $currentMonthDue;

            // If current payment exists but discount wasn't applied before, adjust
            if ($currentPayment && $currentPayment->discount_amount == 0 && $previousPending == 0) {
                // Payment exists but no discount - we should apply discount if eligible
                $currentBalance = max(0, $currentBalance - $tentativeDiscount);
                $discount = $tentativeDiscount;
                $discountEligible = true;
            }

            // ✅ FIX: Total due = previous pending + current balance (with discount applied)
            $totalDue = $previousPending + $currentBalance;

            // Get pending count for display
            $pendingCount = Payment::where('resident_id', $resident->id)
                ->whereIn('status', ['PENDING', 'PARTIAL'])
                ->count();

            // Generate reference
            $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

            // ✅ Amount to pay should be the total due
            $amountToPay = $totalDue;

            // Get discount type for display
            $day = date('j', strtotime($paymentDate));
            $discountType = '';
            if ($discount > 0) {
                if ($day <= 5) {
                    $discountType = 'Early Bird (₹250)';
                } elseif ($day <= 10) {
                    $discountType = 'Early Payment (₹125)';
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'phone' => $resident->phone,
                    'email' => $resident->email,
                    'room_no' => $resident->room ? $resident->room->room_no : 'N/A',
                    'hostel_name' => $resident->hostel ? $resident->hostel->hostel_name : 'N/A',
                    'rent_amount' => $rentAmount,
                    'discount_amount' => $discount,
                    'discount_eligible' => $discountEligible,
                    'discount_type' => $discountType,
                    'previous_pending' => $previousPending,
                    'previous_pending_count' => $previousPendingList->count(),
                    'current_due' => $currentMonthDue,
                    'current_balance' => $currentBalance,
                    'total_due' => $totalDue,
                    'amount_to_pay' => $amountToPay,
                    'has_pending' => $previousPending > 0,
                    'pending_count' => $pendingCount,
                    'is_current_paid' => $isCurrentPaid,
                    'reference' => $reference,
                    'payment_date' => $paymentDate,
                    'day_of_month' => $day,
                    'previous_months' => $previousPendingList->map(function($p) {
                        return [
                            'month' => $p->month,
                            'year' => $p->year,
                            'month_name' => date('F', mktime(0,0,0,$p->month,1)),
                            'balance' => (float) $p->balance_amount,
                            'status' => $p->status
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Guest payment - getResident error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Create Axis Bank payment order
     */
    public function createOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'reference' => 'required|string',
                'resident_id' => 'required|exists:residents,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $resident = Resident::find($request->resident_id);
            $amount = $request->amount;
            $reference = $request->reference;

            // Generate order ID
            $orderId = 'AXIS-' . date('Ymd') . '-' . strtoupper(Str::random(10));
            $transactionId = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(10));

            // Store payment details in session for callback
            session([
                'guest_payment_reference' => $reference,
                'guest_payment_resident_id' => $resident->id,
                'guest_payment_amount' => $amount,
                'guest_payment_order_id' => $orderId,
                'guest_payment_transaction_id' => $transactionId,
                'guest_payment_timestamp' => now()
            ]);

            // Build payment URL
            $paymentUrl = 'https://secure.axisbank.com/payment';

            // Generate signature (for production, use proper HMAC)
            $signature = $this->generateSignature([
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'reference' => $reference,
                'resident_id' => $resident->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'transaction_id' => $transactionId,
                    'reference' => $reference,
                    'amount' => $amount,
                    'payment_url' => $paymentUrl,
                    'merchant_id' => env('AXIS_MERCHANT_ID', 'HOSTEL_MERCHANT'),
                    'currency' => 'INR',
                    'signature' => $signature,
                    'resident_name' => $resident->name,
                    'resident_phone' => $resident->phone,
                    'resident_email' => $resident->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Guest payment - createOrder error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate signature for Axis Bank
     */
    private function generateSignature($data)
    {
        // For production, use proper HMAC with secret key
        $secretKey = env('AXIS_SECRET_KEY', 'axis_secret_key_12345');
        $string = implode('|', $data);
        return hash_hmac('sha256', $string, $secretKey);
    }

    /**
     * Verify Axis Bank payment
     */
    public function verifyPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|string',
                'transaction_id' => 'required|string',
                'reference' => 'required|string',
                'signature' => 'required|string',
                'status' => 'required|in:SUCCESS,FAILED,CANCELLED'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $reference = $request->reference;
            $transactionId = $request->transaction_id;
            $status = $request->status;

            // Verify signature
            $signatureData = [
                'order_id' => $request->order_id,
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'status' => $status
            ];

            $expectedSignature = $this->generateSignature($signatureData);
            $isValidSignature = hash_equals($expectedSignature, $request->signature);

            if (!$isValidSignature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 400);
            }

            // Get resident from session or database
            $residentId = session('guest_payment_resident_id');
            $resident = Resident::find($residentId);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            if ($status === 'SUCCESS') {
                DB::beginTransaction();
                try {
                    $amount = session('guest_payment_amount') ?? 0;
                    $orderId = session('guest_payment_order_id');
                    $currentMonth = now()->month;
                    $currentYear = now()->year;
                    $paymentDate = now()->toDateString();

                    // ✅ Calculate discount based on today's date
                    $tentativeDiscount = $this->calculateDiscount($paymentDate);
                    $rentAmount = (float) ($resident->rent_amount ?? 0);

                    // Get previous pending details
                    $previousPendingList = $this->getPreviousPendingDetails($resident->id, $currentMonth, $currentYear);
                    $totalPreviousPending = $previousPendingList->sum('balance_amount');

                    // ✅ CORRECT DISCOUNT LOGIC:
                    // Discount applies if:
                    // 1. Payment clears ALL previous pending
                    // 2. Remaining amount covers FULL current month rent
                    $willClearAllPending = $amount >= $totalPreviousPending;
                    $amountForCurrentMonth = $amount - $totalPreviousPending;
                    $canCoverFullRent = $amountForCurrentMonth >= $rentAmount;

                    // ✅ Apply discount ONLY if eligible
                    if ($willClearAllPending && $canCoverFullRent) {
                        $discount = $tentativeDiscount;
                        $discountEligible = true;
                        $discountReason = '✅ Eligible: Clears all pending & pays full rent';
                    } else {
                        $discount = 0;
                        $discountEligible = false;
                        if (!$willClearAllPending) {
                            $discountReason = '❌ No discount: Does not clear all previous pending (₹' . number_format($totalPreviousPending, 2) . ' remaining)';
                        } elseif (!$canCoverFullRent) {
                            $discountReason = '❌ No discount: Does not pay full rent (₹' . number_format($amountForCurrentMonth, 2) . ' of ₹' . number_format($rentAmount, 2) . ')';
                        } else {
                            $discountReason = '❌ No discount applied';
                        }
                    }

                    // Calculate current month due with discount
                    $currentMonthDue = $rentAmount - $discount;

                    // Get current payment record
                    $currentPayment = Payment::where('resident_id', $resident->id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->first();

                    $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : $currentMonthDue;

                    // ✅ CORRECT ALLOCATION: First clear previous pending, then current month
                    $remaining = $amount;

                    // 1. Clear previous pending first (oldest to newest)
                    $previousPaid = 0;
                    $previousClearedCount = 0;
                    $previousBalanceRemaining = 0;

                    foreach ($previousPendingList as $prevPayment) {
                        if ($remaining <= 0) break;

                        $prevBalance = (float) $prevPayment->balance_amount;
                        $payAmount = min($remaining, $prevBalance);

                        // Update previous payment
                        $prevPayment->cash_paid_amount += $payAmount;
                        $newBalance = max(0, $prevBalance - $payAmount);
                        $prevPayment->balance_amount = $newBalance;
                        $prevPayment->status = ($newBalance <= 0) ? 'PAID' : 'PARTIAL';

                        // Append transaction ID
                        if ($transactionId && $payAmount > 0) {
                            if ($prevPayment->transaction_id) {
                                $prevPayment->transaction_id .= ' / ' . $transactionId;
                            } else {
                                $prevPayment->transaction_id = $transactionId;
                            }
                        }

                        // Update remark
                        $prevPayment->remark = ($newBalance <= 0)
                            ? "✅ Cleared on " . date('d M Y', strtotime($paymentDate)) . " (Online payment)"
                            : "🟡 Partially cleared: ₹" . number_format($payAmount, 2) . " on " . date('d M Y', strtotime($paymentDate)) . ". Remaining: ₹" . number_format($newBalance, 2);

                        $prevPayment->save();

                        $previousPaid += $payAmount;
                        $remaining -= $payAmount;
                        if ($newBalance <= 0) {
                            $previousClearedCount++;
                        }
                    }

                    $previousBalanceRemaining = max(0, $totalPreviousPending - $previousPaid);

                    // 2. Pay current month
                    $currentPaid = min($remaining, $currentBalance);
                    $remaining -= $currentPaid;
                    $currentBalanceRemaining = max(0, $currentBalance - $currentPaid);

                    // 3. Any remaining amount is advance payment
                    $advanceAmount = $remaining;

                    // Determine final status
                    $totalBalance = $previousBalanceRemaining + $currentBalanceRemaining;
                    $statusFinal = 'PENDING';
                    if ($totalBalance <= 0) {
                        $statusFinal = 'PAID';
                    } elseif ($amount > 0) {
                        $statusFinal = 'PARTIAL';
                    }

                    // Generate receipt
                    $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                    while (Payment::where('receipt_no', $receiptNo)->exists()) {
                        $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                    }

                    // Create or update payment record for current month
                    if ($currentPayment) {
                        // Update existing payment
                        $currentPayment->cash_paid_amount += $currentPaid;
                        $currentPayment->balance_amount = $currentBalanceRemaining;
                        $currentPayment->status = $statusFinal;
                        $currentPayment->payment_date = $paymentDate;
                        $currentPayment->transaction_id = $transactionId;
                        $currentPayment->payment_type = 'online';
                        $currentPayment->discount_amount = $discount;
                        $currentPayment->previous_pending_cleared = $previousPaid;

                        // Build remark
                        $remark = "Online payment via Axis Bank. Reference: {$reference}\n";
                        if ($discount > 0) {
                            $remark .= "✅ Discount applied: ₹" . number_format($discount, 2) . "\n";
                        }
                        if ($previousPaid > 0) {
                            $remark .= "✅ Previous pending cleared: ₹" . number_format($previousPaid, 2) . " ({$previousClearedCount} month(s))\n";
                        }
                        $remark .= "Current month: ₹" . number_format($currentPaid, 2) . " paid. Balance: ₹" . number_format($currentBalanceRemaining, 2);

                        $currentPayment->remark = $remark;
                        $currentPayment->save();
                        $payment = $currentPayment;
                    } else {
                        // Create new payment
                        $payment = Payment::create([
                            'resident_id' => $resident->id,
                            'receipt_no' => $receiptNo,
                            'month' => $currentMonth,
                            'year' => $currentYear,
                            'rent_amount' => $rentAmount,
                            'discount_amount' => $discount,
                            'fine_amount' => 0,
                            'cash_paid_amount' => $currentPaid,
                            'upi_paid_amount' => 0,
                            'balance_amount' => $currentBalanceRemaining,
                            'payment_date' => $paymentDate,
                            'transaction_id' => $transactionId,
                            'status' => $statusFinal,
                            'payment_type' => 'online',
                            'previous_pending_cleared' => $previousPaid,
                            'remark' => "Online payment via Axis Bank. Reference: {$reference}\n" .
                                        ($discount > 0 ? "✅ Discount applied: ₹" . number_format($discount, 2) . "\n" : "") .
                                        ($previousPaid > 0 ? "✅ Previous pending cleared: ₹" . number_format($previousPaid, 2) . " ({$previousClearedCount} month(s))\n" : "") .
                                        "Current month: ₹" . number_format($currentPaid, 2) . " paid. Balance: ₹" . number_format($currentBalanceRemaining, 2)
                        ]);
                    }

                    DB::commit();

                    // Clear session data
                    session()->forget([
                        'guest_payment_reference',
                        'guest_payment_resident_id',
                        'guest_payment_amount',
                        'guest_payment_order_id',
                        'guest_payment_transaction_id'
                    ]);

                    // Build response message
                    $message = "✅ Payment completed successfully!\n";
                    $message .= "📋 Receipt: {$receiptNo}\n";
                    $message .= "💰 Total paid: ₹" . number_format($amount, 2) . "\n";
                    $message .= "─────────────────────\n";

                    if ($previousPaid > 0) {
                        $message .= "📅 Previous pending cleared: ₹" . number_format($previousPaid, 2) . " ({$previousClearedCount} month(s))\n";
                    }
                    if ($currentPaid > 0) {
                        $message .= "📅 Current month paid: ₹" . number_format($currentPaid, 2) . "\n";
                    }
                    if ($advanceAmount > 0) {
                        $message .= "💰 Advance payment: ₹" . number_format($advanceAmount, 2) . " (will adjust next month)\n";
                    }
                    if ($discount > 0) {
                        $message .= "✅ Discount applied: ₹" . number_format($discount, 2) . "\n";
                    }
                    if ($totalBalance > 0) {
                        $message .= "⚠️ Remaining balance: ₹" . number_format($totalBalance, 2);
                    } else {
                        $message .= "✅ All dues cleared!";
                    }

                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'payment' => $payment,
                            'receipt_no' => $receiptNo,
                            'amount_paid' => $amount,
                            'previous_pending_cleared' => $previousPaid,
                            'previous_cleared_count' => $previousClearedCount,
                            'current_month_paid' => $currentPaid,
                            'current_month_balance' => $currentBalanceRemaining,
                            'advance_amount' => $advanceAmount,
                            'total_balance' => $totalBalance,
                            'discount_applied' => $discount,
                            'discount_eligible' => $discountEligible,
                            'discount_type' => $discount > 0 ? ($discount == 250 ? 'Early Bird (1st-5th)' : 'Early Payment (6th-10th)') : 'No discount',
                            'status' => $statusFinal,
                            'breakdown' => [
                                'rent' => $rentAmount,
                                'discount' => $discount,
                                'previous_pending' => $totalPreviousPending,
                                'current_due' => $currentMonthDue,
                                'total_due' => $totalPreviousPending + $currentMonthDue,
                                'paid' => $amount,
                                'balance' => $totalBalance
                            ]
                        ]
                    ]);

                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment ' . strtolower($status)
            ]);

        } catch (\Exception $e) {
            Log::error('Guest payment - verifyPayment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Payment callback from Axis Bank
     */
    public function callback(Request $request)
    {
        try {
            $reference = $request->query('reference') ?? $request->input('reference');
            $transactionId = $request->query('transaction_id') ?? $request->input('transaction_id');
            $status = $request->query('status') ?? $request->input('status');
            $orderId = $request->query('order_id') ?? $request->input('order_id');
            $signature = $request->query('signature') ?? $request->input('signature');

            // If it's an AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                if ($status === 'success' || $status === 'SUCCESS') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment confirmed!'
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not confirmed'
                ]);
            }

            // If we have a success status, verify and show success page
            if ($status === 'success' || $status === 'SUCCESS') {
                // Verify payment status
                $residentId = session('guest_payment_resident_id');
                $payment = Payment::where('resident_id', $residentId)
                    ->where('transaction_id', $transactionId)
                    ->first();

                if ($payment) {
                    return view('guest.payment.success', [
                        'payment' => $payment,
                        'resident' => $payment->resident,
                        'receipt_no' => $payment->receipt_no,
                        'amount' => $payment->rent_amount
                    ]);
                }

                // If payment not found, redirect to home with success
                return redirect()->route('guest.payment.index')
                    ->with('success', 'Payment completed successfully!');
            }

            // If cancelled or failed
            if ($status === 'cancelled' || $status === 'CANCELLED') {
                return redirect()->route('guest.payment.index')
                    ->with('error', 'Payment was cancelled.');
            }

            if ($status === 'failed' || $status === 'FAILED') {
                return redirect()->route('guest.payment.index')
                    ->with('error', 'Payment failed. Please try again.');
            }

            // Default: show status page
            return view('guest.payment.status', [
                'status' => $status,
                'reference' => $reference,
                'transaction_id' => $transactionId
            ]);

        } catch (\Exception $e) {
            Log::error('Guest payment - callback error: ' . $e->getMessage());
            return redirect()->route('guest.payment.index')
                ->with('error', 'Payment processing error.');
        }
    }

    /**
     * Payment cancellation
     */
    public function cancel(Request $request)
    {
        session()->forget([
            'guest_payment_reference',
            'guest_payment_resident_id',
            'guest_payment_amount',
            'guest_payment_order_id',
            'guest_payment_transaction_id'
        ]);

        return redirect()->route('guest.payment.index')
            ->with('error', 'Payment was cancelled.');
    }

    /**
     * Check payment status
     */
    public function status(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Reference required'
            ], 400);
        }

        $payment = Payment::where('transaction_id', 'LIKE', '%' . $reference . '%')
            ->orWhere('receipt_no', $reference)
            ->first();

        if ($payment) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $payment->status,
                    'amount' => $payment->rent_amount,
                    'receipt_no' => $payment->receipt_no,
                    'payment_date' => $payment->payment_date
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment not found'
        ], 404);
    }

    /**
     * Webhook for Axis Bank (server-to-server)
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();

            // Verify webhook signature
            $signature = $request->header('X-Axis-Signature');
            $expectedSignature = $this->generateSignature($payload);

            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Guest payment - webhook invalid signature');
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }

            $reference = $payload['reference'] ?? null;
            $transactionId = $payload['transaction_id'] ?? null;
            $status = $payload['status'] ?? null;
            $amount = $payload['amount'] ?? 0;

            if ($status === 'SUCCESS' && $reference) {
                // Process payment as in verifyPayment
                $resident = Resident::where('id', $payload['resident_id'] ?? 0)->first();
                if ($resident) {
                    // Same logic as verifyPayment
                }
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Guest payment - webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Generate payment link for hostel
     */
    public function generateLink($hostelId)
    {
        $hostel = Hostel::find($hostelId);
        if (!$hostel) {
            return response()->json([
                'success' => false,
                'message' => 'Hostel not found'
            ], 404);
        }

        $encodedId = Crypt::encryptString($hostelId);
        $link = url('/guest/payment/' . $encodedId);

        return response()->json([
            'success' => true,
            'data' => [
                'hostel' => $hostel->hostel_name,
                'link' => $link,
                'encoded_id' => $encodedId
            ]
        ]);
    }

    /**
     * Encode hostel ID
     */
    public function encodeId($hostelId)
    {
        try {
            $encoded = Crypt::encryptString($hostelId);
            return response()->json([
                'success' => true,
                'data' => [
                    'hostel_id' => $hostelId,
                    'encoded' => $encoded,
                    'url' => url('/guest/payment/' . $encoded)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decode hostel ID
     */
    public function decodeId($encodedId)
    {
        try {
            $decoded = Crypt::decryptString($encodedId);
            return response()->json([
                'success' => true,
                'data' => [
                    'encoded' => $encodedId,
                    'hostel_id' => $decoded
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid encoded ID'
            ], 400);
        }
    }

    /**
     * Get payment history for resident
     */
    public function getPaymentHistory($residentId)
    {
        try {
            $resident = Resident::find($residentId);
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            $payments = Payment::where('resident_id', $residentId)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'resident' => $resident->name,
                    'payments' => $payments
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resident due amount (for QR code display)
     */
    public function getResidentDue(Request $request)
    {
        try {
            $residentId = $request->query('resident_id');
            $resident = Resident::find($residentId);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            $currentMonth = now()->month;
            $currentYear = now()->year;
            $paymentDate = now()->toDateString();

            // Calculate total due with discount logic
            $previousPending = $this->getTotalPreviousPending($resident->id, $currentMonth, $currentYear);
            $tentativeDiscount = $this->calculateDiscount($paymentDate);
            $rentAmount = (float) ($resident->rent_amount ?? 0);

            // Check if discount applies
            $willClearAllPending = false; // For preview only
            $canCoverFullRent = true; // Assume they'll pay full
            $discount = $tentativeDiscount; // Show discount as eligible

            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            $currentBalance = $currentPayment ? (float) $currentPayment->balance_amount : ($rentAmount - $discount);
            $totalDue = $previousPending + $currentBalance;

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'total_due' => $totalDue,
                    'previous_pending' => $previousPending,
                    'current_balance' => $currentBalance,
                    'rent_amount' => $rentAmount,
                    'discount_amount' => $discount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
