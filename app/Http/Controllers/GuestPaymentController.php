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

            // Calculate discount based on current date
            $day = date('j', strtotime($paymentDate));
            $discount = 0;
            if ($day <= 5) {
                $discount = 250;
            } elseif ($day <= 10) {
                $discount = 125;
            }

            // Get previous pending (unpaid or partially paid from previous months)
            $previousPending = Payment::where('resident_id', $resident->id)
                ->where(function($q) use ($currentMonth, $currentYear) {
                    $q->where('year', '<', $currentYear)
                      ->orWhere(function($q2) use ($currentMonth, $currentYear) {
                          $q2->where('year', $currentYear)
                             ->where('month', '<', $currentMonth);
                      });
                })
                ->whereIn('status', ['PENDING', 'PARTIAL'])
                ->sum('balance_amount');

            // Calculate current month rent with discount
            $currentMonthRent = $resident->rent_amount - $discount;

            // Check if already paid for current month
            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            // Get current balance (if payment exists, use its balance, otherwise use full rent)
            $currentBalance = $currentPayment ? $currentPayment->balance_amount : $currentMonthRent;

            // FIX: Total due should be previous pending + current balance
            $totalDue = $previousPending + $currentBalance;

            // Get pending count for display
            $pendingCount = Payment::where('resident_id', $resident->id)
                ->whereIn('status', ['PENDING', 'PARTIAL'])
                ->count();

            // Check if current month is already paid
            $isCurrentPaid = $currentPayment && $currentPayment->status == 'PAID';

            // Generate reference
            $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

            // FIX: Amount to pay should be the total due
            $amountToPay = $totalDue;

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'phone' => $resident->phone,
                    'email' => $resident->email,
                    'room_no' => $resident->room ? $resident->room->room_no : 'N/A',
                    'hostel_name' => $resident->hostel ? $resident->hostel->hostel_name : 'N/A',
                    'rent_amount' => $resident->rent_amount,
                    'discount_amount' => $discount,
                    'previous_pending' => $previousPending,
                    'current_due' => $currentMonthRent,
                    'current_balance' => $currentBalance,
                    'total_due' => $totalDue,
                    'amount_to_pay' => $amountToPay,
                    'has_pending' => $previousPending > 0,
                    'pending_count' => $pendingCount,
                    'is_current_paid' => $isCurrentPaid,
                    'reference' => $reference,
                    'payment_date' => $paymentDate,
                    'day_of_month' => $day,
                    'discount_applied' => $discount > 0,
                    'discount_type' => $discount > 0 ? ($day <= 5 ? 'Early Bird (₹250)' : 'Early Payment (₹125)') : 'No discount'
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

                    // Calculate discount based on current date
                    $day = date('j');
                    $discount = 0;
                    if ($day <= 5) {
                        $discount = 250;
                    } elseif ($day <= 10) {
                        $discount = 125;
                    }

                    // Get previous pending (unpaid or partially paid from previous months)
                    $previousPending = Payment::where('resident_id', $resident->id)
                        ->where(function($q) use ($currentMonth, $currentYear) {
                            $q->where('year', '<', $currentYear)
                              ->orWhere(function($q2) use ($currentMonth, $currentYear) {
                                  $q2->where('year', $currentYear)
                                     ->where('month', '<', $currentMonth);
                              });
                        })
                        ->whereIn('status', ['PENDING', 'PARTIAL'])
                        ->sum('balance_amount');

                    // Calculate current month due with discount
                    $currentMonthRent = $resident->rent_amount - $discount;

                    // Get current payment record
                    $currentPayment = Payment::where('resident_id', $resident->id)
                        ->where('month', $currentMonth)
                        ->where('year', $currentYear)
                        ->first();

                    $currentBalance = $currentPayment ? $currentPayment->balance_amount : $currentMonthRent;

                    // Total due = previous pending + current balance
                    $totalDue = $previousPending + $currentBalance;

                    // Allocate payment: First clear previous pending, then current month
                    $remaining = $amount;
                    
                    // 1. Clear previous pending first
                    $previousPaid = 0;
                    $previousBalanceRemaining = 0;
                    
                    if ($previousPending > 0 && $remaining > 0) {
                        $previousPaid = min($remaining, $previousPending);
                        $remaining -= $previousPaid;
                        $previousBalanceRemaining = $previousPending - $previousPaid;
                    }

                    // 2. Pay current month
                    $currentPaid = 0;
                    $currentBalanceRemaining = 0;
                    
                    if ($currentBalance > 0 && $remaining > 0) {
                        $currentPaid = min($remaining, $currentBalance);
                        $remaining -= $currentPaid;
                        $currentBalanceRemaining = $currentBalance - $currentPaid;
                    }

                    // 3. Any remaining amount is advance payment
                    $advanceAmount = $remaining;

                    // Update previous pending payments
                    if ($previousPaid > 0) {
                        $prevPayments = Payment::where('resident_id', $resident->id)
                            ->where(function($q) use ($currentMonth, $currentYear) {
                                $q->where('year', '<', $currentYear)
                                  ->orWhere(function($q2) use ($currentMonth, $currentYear) {
                                      $q2->where('year', $currentYear)
                                         ->where('month', '<', $currentMonth);
                                  });
                            })
                            ->whereIn('status', ['PENDING', 'PARTIAL'])
                            ->orderBy('year', 'asc')
                            ->orderBy('month', 'asc')
                            ->get();

                        $remainingPrev = $previousPaid;
                        foreach ($prevPayments as $prev) {
                            if ($remainingPrev <= 0) break;
                            $prevBalance = $prev->balance_amount;
                            $payAmount = min($remainingPrev, $prevBalance);
                            $prev->cash_paid_amount += $payAmount;
                            $prev->balance_amount = max(0, $prevBalance - $payAmount);
                            $prev->status = ($prev->balance_amount <= 0) ? 'PAID' : 'PARTIAL';
                            $prev->save();
                            $remainingPrev -= $payAmount;
                        }
                    }

                    // Generate receipt
                    $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                    while (Payment::where('receipt_no', $receiptNo)->exists()) {
                        $receiptNo = 'RCPT-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                    }

                    // Determine status for current month
                    $statusFinal = ($currentBalanceRemaining <= 0) ? 'PAID' : 'PARTIAL';

                    // Create or update payment record for current month
                    if ($currentPayment) {
                        // Update existing payment
                        $currentPayment->cash_paid_amount += $currentPaid;
                        $currentPayment->balance_amount = $currentBalanceRemaining;
                        $currentPayment->status = $statusFinal;
                        $currentPayment->payment_date = now()->toDateString();
                        $currentPayment->transaction_id = $transactionId;
                        $currentPayment->payment_type = 'online';
                        $currentPayment->remark = "Online payment via Axis Bank. Reference: {$reference}";
                        $currentPayment->save();
                        $payment = $currentPayment;
                    } else {
                        // Create new payment
                        $payment = Payment::create([
                            'resident_id' => $resident->id,
                            'receipt_no' => $receiptNo,
                            'month' => $currentMonth,
                            'year' => $currentYear,
                            'rent_amount' => $resident->rent_amount,
                            'discount_amount' => $discount,
                            'fine_amount' => 0,
                            'cash_paid_amount' => $currentPaid,
                            'upi_paid_amount' => 0,
                            'balance_amount' => $currentBalanceRemaining,
                            'payment_date' => now()->toDateString(),
                            'transaction_id' => $transactionId,
                            'status' => $statusFinal,
                            'payment_type' => 'online',
                            'previous_pending_cleared' => $previousPaid,
                            'remark' => "Online payment via Axis Bank. Reference: {$reference}"
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

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment completed successfully!',
                        'data' => [
                            'payment' => $payment,
                            'receipt_no' => $receiptNo,
                            'amount_paid' => $amount,
                            'previous_pending_cleared' => $previousPaid,
                            'current_month_paid' => $currentPaid,
                            'advance_amount' => $advanceAmount,
                            'total_due' => $totalDue,
                            'remaining_balance' => $currentBalanceRemaining + $previousBalanceRemaining
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
                    // Create payment record...
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

            // Calculate total due
            $previousPending = Payment::where('resident_id', $resident->id)
                ->where(function($q) use ($currentMonth, $currentYear) {
                    $q->where('year', '<', $currentYear)
                      ->orWhere(function($q2) use ($currentMonth, $currentYear) {
                          $q2->where('year', $currentYear)
                             ->where('month', '<', $currentMonth);
                      });
                })
                ->whereIn('status', ['PENDING', 'PARTIAL'])
                ->sum('balance_amount');

            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            $currentBalance = $currentPayment ? $currentPayment->balance_amount : $resident->rent_amount;
            $totalDue = $previousPending + $currentBalance;

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'total_due' => $totalDue,
                    'previous_pending' => $previousPending,
                    'current_balance' => $currentBalance,
                    'rent_amount' => $resident->rent_amount
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