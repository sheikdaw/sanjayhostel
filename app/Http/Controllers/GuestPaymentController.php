<?php
// app/Http/Controllers/GuestPaymentController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class GuestPaymentController extends Controller
{
    protected RazorpayService $razorpay;

    public function __construct(RazorpayService $razorpay)
    {
        $this->razorpay = $razorpay;
    }

    /**
     * Display the guest payment page.
     * GET /guest/payment/{encodedHostelId?}
     */
    public function index(Request $request, $encodedHostelId = null)
    {
        $hostelId = null;

        if ($encodedHostelId) {
            try {
                $hostelId = Crypt::decryptString($encodedHostelId);
            } catch (Exception $e) {
                if (is_numeric($encodedHostelId)) {
                    $hostelId = $encodedHostelId;
                } else {
                    abort(404, 'Invalid payment link');
                }
            }
        }

        $hostel = null;
        if ($hostelId) {
            $hostel = Hostel::with('roomTypes')->find($hostelId);
            if (!$hostel) {
                abort(404, 'Hostel not found');
            }
        }

        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        $encodedId = $hostelId ? Crypt::encryptString($hostelId) : null;

        return view('guest.payment', compact(
            'hostel',
            'hostelId',
            'reference',
            'encodedHostelId',
            'encodedId'
        ));
    }

    /**
     * Get resident details by mobile number.
     * POST /guest/payment/resident
     */
    public function getResident(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|min:10|max:15',
            'hostel_id' => 'required|exists:hostels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $resident = Resident::where('phone', $request->mobile)
            ->where('hostel_id', $request->hostel_id)
            ->where('status', 'ACTIVE')
            ->with('room')
            ->first();

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'No resident found with this mobile number in this hostel.'
            ], 404);
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentDay = now()->day;

        // Get all payments for this resident
        $allPayments = Payment::where('resident_id', $resident->id)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Check if current month's payment exists
        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // Get pending payments
        $pendingPayments = Payment::where('resident_id', $resident->id)
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Check if current month is already paid
        $isCurrentMonthPaid = false;
        $currentMonthStatus = 'PENDING';

        if ($currentPayment) {
            if ($currentPayment->status === 'PAID') {
                $isCurrentMonthPaid = true;
                $currentMonthStatus = 'PAID';
            } elseif ($currentPayment->status === 'PARTIAL') {
                $currentMonthStatus = 'PARTIAL';
            }
        }

        // Calculate payment breakdown
        $rentAmount = (float) ($resident->rent_amount ?? 0);
        $totalDue = $pendingPayments->sum('balance_amount');

        if (!$isCurrentMonthPaid && $currentMonthStatus !== 'PAID') {
            if (!$currentPayment) {
                $totalDue += $rentAmount;
            } else if ($currentPayment->status === 'PARTIAL') {
                $totalDue += $currentPayment->balance_amount;
            }
        }

        // Discount calculation
        $discount = 0;
        $discountType = null;
        $discountAmount = 0;
        $finalAmount = (float) $totalDue;
        $discountMessage = '';

        if ($pendingPayments->count() == 0 && !$isCurrentMonthPaid && $totalDue > 0) {
            if ($currentDay >= 1 && $currentDay <= 5) {
                $discountAmount = min(250, $rentAmount * 0.10);
                $discountType = 'early_discount_250';
                $discountMessage = 'Early payment discount (1st-5th): 10% off up to ₹250';
            } elseif ($currentDay >= 6 && $currentDay <= 10) {
                $discountAmount = min(125, $rentAmount * 0.05);
                $discountType = 'early_discount_125';
                $discountMessage = 'Early payment discount (6th-10th): 5% off up to ₹125';
            } else {
                $discountAmount = 0;
                $discountType = 'no_discount';
                $discountMessage = 'No discount available. Please pay before 10th for early payment discount.';
            }
            $discount = $discountAmount;
            $finalAmount = max(0, $totalDue - $discount);
        } else {
            $discount = 0;
            $discountType = 'no_discount';
            $finalAmount = $totalDue;
            
            if ($isCurrentMonthPaid) {
                $discountMessage = '✅ This month\'s rent is already paid.';
            } elseif ($pendingPayments->count() > 0) {
                $discountMessage = '⚠️ Previous pending payments found. No discount applicable.';
            } else {
                $discountMessage = 'No discount applicable.';
            }
        }

        // Fine calculation
        $fineAmount = 0;
        $fineMessage = '';
        
        if (!$isCurrentMonthPaid && $currentDay > 10 && $totalDue > 0) {
            $daysLate = $currentDay - 10;
            $fineAmount = $daysLate * 0;
            $fineMessage = "Late fee: ₹00 per day after 10th ({$daysLate} days late)";
        }

        // Calculate paid amounts
        $totalUPIPaid = 0;
        $totalCashPaid = 0;
        $totalPaidAmount = 0;
        $totalBalance = 0;
        $paymentBreakdown = [];

        foreach ($allPayments as $payment) {
            $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
            $upiPaid = (float) ($payment->upi_paid_amount ?? 0);
            $cashPaid = (float) ($payment->cash_paid_amount ?? 0);
            $balance = (float) ($payment->balance_amount ?? 0);
            $rentAmt = (float) ($payment->rent_amount ?? 0);
            
            $totalUPIPaid += $upiPaid;
            $totalCashPaid += $cashPaid;
            $totalPaidAmount += ($upiPaid + $cashPaid);
            $totalBalance += $balance;

            $paymentBreakdown[] = [
                'month' => $monthName . ' ' . $payment->year,
                'rent_amount' => $rentAmt,
                'discount' => (float) ($payment->discount_amount ?? 0),
                'fine' => (float) ($payment->fine_amount ?? 0),
                'upi_paid' => $upiPaid,
                'cash_paid' => $cashPaid,
                'total_paid' => $upiPaid + $cashPaid,
                'balance' => $balance,
                'status' => $payment->status
            ];
        }

        // Payment status message
        $paymentStatusMessage = '';
        $paymentStatus = '';

        if ($isCurrentMonthPaid && $totalDue == 0 && $totalBalance == 0) {
            $paymentStatus = 'PAID';
            $paymentStatusMessage = '✅ All payments are up to date! You have no pending dues.';
        } elseif ($pendingPayments->count() > 0 || $totalBalance > 0) {
            $paymentStatus = 'PENDING';
            $paymentStatusMessage = '⚠️ You have pending payment(s). Please clear your dues.';
        } elseif (!$isCurrentMonthPaid && $totalDue > 0) {
            $paymentStatus = 'PENDING';
            $paymentStatusMessage = '📝 You have pending payment for this month.';
        } else {
            $paymentStatus = 'PAID';
            $paymentStatusMessage = '✅ All payments are up to date!';
        }

        $amountToPay = $finalAmount + $fineAmount;
        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => (int) $resident->id,
                'name' => $resident->name,
                'email' => $resident->email ?? 'Not provided',
                'phone' => $resident->phone,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'rent_amount' => (float) $rentAmount,
                'total_due' => (float) $totalDue,
                'discount' => (float) $discount,
                'discount_type' => $discountType,
                'discount_amount' => (float) $discount,
                'discount_message' => $discountMessage,
                'discount_applicable' => $discount > 0,
                'fine_amount' => (float) $fineAmount,
                'fine_message' => $fineMessage,
                'final_amount' => (float) $amountToPay,
                'amount_to_pay' => (float) $amountToPay,
                'total_upi_paid' => (float) $totalUPIPaid,
                'total_cash_paid' => (float) $totalCashPaid,
                'total_paid_amount' => (float) $totalPaidAmount,
                'total_balance' => (float) $totalBalance,
                'payment_status' => $paymentStatus,
                'payment_status_message' => $paymentStatusMessage,
                'is_paid' => $paymentStatus === 'PAID',
                'has_pending' => $pendingPayments->count() > 0 || $totalBalance > 0,
                'pending_count' => (int) $pendingPayments->count(),
                'current_day' => $currentDay,
                'current_month_status' => $currentMonthStatus,
                'reference' => $reference,
                'payment_breakdown' => $paymentBreakdown,
                'pending_payments' => $paymentBreakdown,
                'message' => $paymentStatusMessage
            ]
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Create a Razorpay payment order and return order details
     * POST /guest/payment/create-order
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'reference' => 'required|string',
            'resident_id' => 'required|exists:residents,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $amount = (float) $request->amount;
        $reference = $request->reference;
        $residentId = $request->resident_id;

        $resident = Resident::find($residentId);
        $roomNo = $resident && $resident->room ? ($resident->room->room_no ?? 'N/A') : 'N/A';
        $residentName = $resident ? $resident->name : 'Resident';

        // Store resident ID for webhook/callback
        cache()->put('razorpay_order_resident_' . $reference, $residentId, now()->addHours(2));

        try {
            $orderData = [
                'receipt' => $reference,
                'amount' => $amount,
                'currency' => 'INR',
                'notes' => [
                    'resident_id' => $residentId,
                    'resident_name' => $residentName,
                    'room_no' => $roomNo,
                    'payment_type' => 'rent'
                ]
            ];

            $result = $this->razorpay->createOrder($orderData);

            return response()->json([
                'success' => true,
                'order_id' => $result['order_id'],
                'amount' => $result['amount'],
                'currency' => $result['currency'],
                'key_id' => $result['key_id'],
                'reference' => $reference,
                'resident_id' => $residentId,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment after successful Razorpay checkout
     * POST /guest/payment/verify
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $payload = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ];

        try {
            // Verify signature
            $isValid = $this->razorpay->verifyPaymentSignature($payload);

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed: Invalid signature'
                ], 400);
            }

            // Get payment details
            $payment = $this->razorpay->fetchPayment($request->razorpay_payment_id);
            $order = $this->razorpay->fetchOrder($request->razorpay_order_id);

            // Record payment if status is captured
            if ($payment['status'] === 'captured') {
                $paymentRecord = $this->recordPaymentIfNeeded(
                    $request->reference,
                    $payment,
                    $order
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'data' => [
                        'payment_id' => $payment['id'],
                        'amount' => $payment['amount'] / 100,
                        'status' => $payment['status'],
                        'receipt_no' => $paymentRecord->receipt_no ?? $request->reference,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment is not captured. Status: ' . $payment['status']
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browser callback after payment
     * GET /guest/payment/callback
     */
    public function callback(Request $request)
    {
        $orderId = $request->query('order_id');
        $paymentId = $request->query('payment_id');
        $reference = $request->query('reference');

        if (!$orderId || !$reference) {
            return view('guest.payment-result', [
                'success' => false,
                'message' => 'Missing payment reference.',
            ]);
        }

        try {
            // Fetch payment details
            $payment = $this->razorpay->fetchPayment($paymentId);
            $order = $this->razorpay->fetchOrder($orderId);

            $state = $payment['status'] ?? 'UNKNOWN';

            if ($state === 'captured') {
                $paymentRecord = $this->recordPaymentIfNeeded($reference, $payment, $order);

                return view('guest.payment-result', [
                    'success' => true,
                    'message' => 'Payment successful!',
                    'reference' => $reference,
                    'amount' => ($payment['amount'] ?? 0) / 100,
                    'receipt_no' => $paymentRecord->receipt_no ?? $reference,
                ]);
            }

            if ($state === 'pending' || $state === 'authorized') {
                return view('guest.payment-result', [
                    'success' => null,
                    'message' => 'Your payment is still processing. Please check back later.',
                    'reference' => $reference,
                ]);
            }

            return view('guest.payment-result', [
                'success' => false,
                'message' => 'Payment was not completed (' . $state . '). You can try again.',
                'reference' => $reference,
            ]);
        } catch (Exception $e) {
            return view('guest.payment-result', [
                'success' => false,
                'message' => 'Could not verify payment. Reference: ' . $reference,
                'reference' => $reference,
            ]);
        }
    }

    /**
     * AJAX polling for payment status
     * GET /guest/payment/status?reference=PAY-...
     */
    public function status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reference = $request->reference;

        // Check local payment record
        $payment = Payment::where('receipt_no', $reference)->with('resident')->first();
        if ($payment) {
            return response()->json([
                'success' => true,
                'state' => 'COMPLETED',
                'data' => [
                    'status' => $payment->status,
                    'amount' => $payment->upi_paid_amount,
                    'receipt_no' => $payment->receipt_no,
                    'payment_date' => $payment->payment_date->format('d M Y h:i A'),
                    'resident' => $payment->resident->name ?? 'N/A'
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'state' => 'PENDING',
        ]);
    }

    /**
     * Razorpay Webhook
     * POST /guest/payment/webhook
     */
    public function webhook(Request $request)
    {
        // Verify webhook signature
        $webhookSecret = config('razorpay.webhook_secret');
        $signature = $request->header('X-Razorpay-Signature');

        if (!$signature || !$webhookSecret) {
            Log::warning('Razorpay webhook: missing signature or secret');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify signature
        $body = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $body, $webhookSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay webhook: invalid signature');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? null;
        $payment = $payload['payload']['payment']['entity'] ?? [];
        $order = $payload['payload']['order']['entity'] ?? [];

        Log::info('Razorpay webhook received', [
            'event' => $event,
            'payment_id' => $payment['id'] ?? null,
            'order_id' => $order['id'] ?? null,
        ]);

        // Handle payment captured event
        if ($event === 'payment.captured' && !empty($payment['id'])) {
            $receipt = $payment['receipt'] ?? $order['receipt'] ?? null;
            if ($receipt) {
                $this->recordPaymentIfNeeded($receipt, $payment, $order);
            }
        }

        // Must return 2xx quickly
        return response()->json(['status' => 'ok']);
    }

    /**
     * Idempotently creates the local Payment record
     */
    protected function recordPaymentIfNeeded(string $receipt, array $payment, array $order): Payment
    {
        $existing = Payment::where('receipt_no', $receipt)->first();
        if ($existing) {
            return $existing;
        }

        $residentId = cache()->get('razorpay_order_resident_' . $receipt);
        $resident = $residentId ? Resident::find($residentId) : null;

        // Get resident ID from order notes if not in cache
        if (!$resident && isset($order['notes']['resident_id'])) {
            $resident = Resident::find($order['notes']['resident_id']);
        }

        $paidAmount = ($payment['amount'] ?? 0) / 100;
        $rentAmount = $resident?->rent_amount ?? 0;
        $transactionId = $payment['id'] ?? ('TXN-' . strtoupper(Str::random(10)));

        $paymentStatus = $this->determinePaymentStatus($paidAmount, $rentAmount);

        return Payment::create([
            'resident_id' => $resident?->id,
            'receipt_no' => $receipt,
            'month' => now()->month,
            'year' => now()->year,
            'rent_amount' => $rentAmount,
            'discount_amount' => 0,
            'fine_amount' => 0,
            'cash_paid_amount' => 0,
            'upi_paid_amount' => $paidAmount,
            'balance_amount' => max(0, $rentAmount - $paidAmount),
            'payment_date' => now(),
            'transaction_id' => $transactionId,
            'status' => $paymentStatus,
        ]);
    }

    /**
     * Determine payment status based on paid amount vs total amount
     */
    protected function determinePaymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($totalAmount <= 0) {
            return 'PAID';
        }

        if ($paidAmount >= $totalAmount) {
            return 'PAID';
        } elseif ($paidAmount > 0) {
            return 'PARTIAL';
        } else {
            return 'PENDING';
        }
    }

    /**
     * Admin helper: generate an encoded hostel payment link.
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
        $url = url('/guest/payment/' . $encodedId);

        return response()->json([
            'success' => true,
            'data' => [
                'hostel_id' => $hostelId,
                'hostel_name' => $hostel->hostel_name,
                'hostel_code' => $hostel->hostel_code ?? 'HOSTEL',
                'encoded_id' => $encodedId,
                'payment_link' => $url,
            ]
        ]);
    }

    public function encodeId($hostelId)
    {
        try {
            $encoded = Crypt::encryptString($hostelId);
            return response()->json([
                'success' => true,
                'data' => [
                    'original_id' => $hostelId,
                    'encoded_id' => $encoded,
                    'url' => url('/guest/payment/' . $encoded)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to encode ID: ' . $e->getMessage()
            ], 500);
        }
    }

    public function decodeId($encodedId)
    {
        try {
            $decoded = Crypt::decryptString($encodedId);
            return response()->json([
                'success' => true,
                'data' => [
                    'encoded_id' => $encodedId,
                    'decoded_id' => $decoded
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decode ID: ' . $e->getMessage()
            ], 500);
        }
    }
}