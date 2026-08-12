<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use App\Services\PhonePeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class GuestPaymentController extends Controller
{
    protected PhonePeService $phonePe;

    public function __construct(PhonePeService $phonePe)
    {
        $this->phonePe = $phonePe;
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

        // Check if current month's payment exists
        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // Get all pending payments (previous months)
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

        // Calculate total due from pending payments
        $totalDue = $pendingPayments->sum('balance_amount');
        
        // Get rent amount
        $rentAmount = (float) ($resident->rent_amount ?? 0);
        
        // Check if current month is not paid, add to total due
        if (!$isCurrentMonthPaid && $currentMonthStatus !== 'PAID') {
            // If there's no current payment record, create one mentally
            if (!$currentPayment) {
                $totalDue += $rentAmount;
            } else if ($currentPayment->status === 'PARTIAL') {
                // If partial payment, add remaining balance
                $totalDue += $currentPayment->balance_amount;
            }
        }

        // ============================================================
        // DISCOUNT CALCULATION BASED ON DATE
        // ============================================================
        $discount = 0;
        $discountType = null;
        $finalAmount = (float) $totalDue;
        $discountMessage = '';

        // Only apply discount if there are no pending payments from previous months
        // AND current month is not paid yet
        if ($pendingPayments->count() == 0 && !$isCurrentMonthPaid && $totalDue > 0) {
            
            // Check current date for discount
            if ($currentDay >= 1 && $currentDay <= 5) {
                // 1st to 5th: 10% discount up to ₹250
                $discount = min(250, $rentAmount * 0.10);
                $discountType = 'early_discount_250';
                $discountMessage = 'Early payment discount (1st-5th): 10% off up to ₹250';
                
            } elseif ($currentDay >= 6 && $currentDay <= 10) {
                // 6th to 10th: 5% discount up to ₹125
                $discount = min(125, $rentAmount * 0.05);
                $discountType = 'early_discount_125';
                $discountMessage = 'Early payment discount (6th-10th): 5% off up to ₹125';
                
            } else {
                // After 10th: No discount
                $discount = 0;
                $discountType = 'no_discount';
                $discountMessage = 'No discount available. Please pay before 10th for early payment discount.';
            }
            
            // Calculate final amount after discount
            $finalAmount = max(0, $totalDue - $discount);
        } else {
            // If there are pending payments or already paid, no discount
            $discount = 0;
            $discountType = 'no_discount';
            $discountMessage = $isCurrentMonthPaid ? 'This month\'s rent is already paid.' : 'Previous pending payments, no discount applicable.';
        }

        // ============================================================
        // PAYMENT STATUS MESSAGE
        // ============================================================
        $paymentStatusMessage = '';
        $paymentStatus = '';

        if ($isCurrentMonthPaid && $totalDue == 0) {
            $paymentStatus = 'PAID';
            $paymentStatusMessage = '✅ All payments are up to date! You have no pending dues.';
        } elseif ($pendingPayments->count() > 0) {
            $paymentStatus = 'PENDING';
            $paymentStatusMessage = '⚠️ You have ' . $pendingPayments->count() . ' pending payment(s) from previous months.';
        } elseif (!$isCurrentMonthPaid && $totalDue > 0) {
            $paymentStatus = 'PENDING';
            $paymentStatusMessage = '📝 You have pending payment for this month.';
        } else {
            $paymentStatus = 'PAID';
            $paymentStatusMessage = '✅ All payments are up to date!';
        }

        // Prepare pending details for display
        $pendingDetails = [];
        foreach ($pendingPayments as $payment) {
            $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
            $pendingDetails[] = [
                'month' => $monthName . ' ' . $payment->year,
                'amount' => (float) $payment->balance_amount,
                'status' => $payment->status,
                'total_amount' => (float) $payment->rent_amount,
                'paid_amount' => (float) ($payment->upi_paid_amount + $payment->cash_paid_amount)
            ];
        }

        // Generate payment reference
        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

        // ============================================================
        // RESPONSE DATA
        // ============================================================
        $responseData = [
            'resident_id' => (int) $resident->id,
            'name' => $resident->name,
            'email' => $resident->email ?? 'Not provided',
            'phone' => $resident->phone,
            'room_no' => $resident->room->room_no ?? 'N/A',
            'rent_amount' => (float) $rentAmount,
            'total_due' => (float) $totalDue,
            'final_amount' => (float) $finalAmount,
            'discount' => (float) $discount,
            'discount_type' => $discountType,
            'discount_message' => $discountMessage,
            'pending_count' => (int) $pendingPayments->count(),
            'reference' => $reference,
            'has_pending' => $pendingPayments->count() > 0,
            'is_paid' => $isCurrentMonthPaid && $totalDue == 0,
            'payment_status' => $paymentStatus,
            'payment_status_message' => $paymentStatusMessage,
            'current_month_status' => $currentMonthStatus,
            'current_day' => $currentDay,
            'pending_payments' => $pendingDetails,
            'discount_applicable' => $discount > 0,
            'message' => $paymentStatusMessage
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Create a real PhonePe payment order and return the hosted
     * checkout redirect URL.
     * GET /guest/payment/generate-qr
     */
    public function generateQR(Request $request)
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
        $residentId = (int) $request->resident_id;

        $resident = Resident::with('room')->find($residentId);
        $roomNo = $resident && $resident->room ? ($resident->room->room_no ?? 'N/A') : 'N/A';
        $residentName = $resident ? $resident->name : 'Resident';

        // Remember which resident this order belongs to
        cache()->put('phonepe_order_resident_' . $reference, $residentId, now()->addHours(2));

        try {
            $result = $this->phonePe->createPayment(
                $reference,
                (int) round($amount * 100), // rupees -> paise
                route('guest.payment.callback', ['merchant_order_id' => $reference]),
                "Rent - {$residentName} - Room {$roomNo}"
            );

            return response()->json([
                'success' => true,
                'redirect_url' => $result['redirectUrl'] ?? null,
                'order_id' => $result['orderId'] ?? null,
                'state' => $result['state'] ?? 'PENDING',
                'reference' => $reference,
                'amount' => $amount,
                'resident_name' => $residentName,
                'room_no' => $roomNo,
            ]);
        } catch (Exception $e) {
            Log::error('PhonePe payment creation failed: ' . $e->getMessage(), [
                'reference' => $reference,
                'resident_id' => $residentId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browser lands here after PhonePe checkout.
     * GET /guest/payment/callback
     */
    public function callback(Request $request)
    {
        $merchantOrderId = $request->query('merchant_order_id');

        if (!$merchantOrderId) {
            return view('guest.payment-result', [
                'success' => false,
                'message' => 'Missing payment reference.',
            ]);
        }

        try {
            $status = $this->phonePe->orderStatus($merchantOrderId, true);
        } catch (Exception $e) {
            Log::error('PhonePe callback status check failed: ' . $e->getMessage(), [
                'merchant_order_id' => $merchantOrderId
            ]);

            return view('guest.payment-result', [
                'success' => false,
                'message' => 'Could not verify payment. Reference: ' . $merchantOrderId,
                'reference' => $merchantOrderId,
            ]);
        }

        $state = $status['state'] ?? 'UNKNOWN';

        if ($state === 'COMPLETED') {
            $payment = $this->recordPaymentIfNeeded($merchantOrderId, $status);

            return view('guest.payment-result', [
                'success' => true,
                'message' => 'Payment successful!',
                'reference' => $merchantOrderId,
                'amount' => ($status['amount'] ?? 0) / 100,
                'receipt_no' => $payment->receipt_no ?? $merchantOrderId,
            ]);
        }

        if ($state === 'PENDING') {
            return view('guest.payment-result', [
                'success' => null,
                'message' => 'Your payment is still processing. This page will update automatically.',
                'reference' => $merchantOrderId,
            ]);
        }

        return view('guest.payment-result', [
            'success' => false,
            'message' => 'Payment was not completed (' . $state . '). You can try again.',
            'reference' => $merchantOrderId,
        ]);
    }

    /**
     * AJAX polling / lookup endpoint.
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

        // Fast path: already recorded locally
        $payment = Payment::where('receipt_no', $reference)->with('resident')->first();
        if ($payment && $payment->status === 'PAID') {
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

        try {
            $status = $this->phonePe->orderStatus($reference, true);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        $state = $status['state'] ?? 'UNKNOWN';

        if ($state === 'COMPLETED') {
            $payment = $this->recordPaymentIfNeeded($reference, $status);

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
            'state' => $state,
        ]);
    }

    /**
     * PhonePe Webhook (S2S callback)
     * POST /guest/payment/webhook
     */
    public function webhook(Request $request)
    {
        $authHeader = $request->header('Authorization', '');

        $expected = hash('sha256',
            config('phonepe.webhook_username') . ':' . config('phonepe.webhook_password')
        );

        if (!hash_equals($expected, $authHeader)) {
            Log::warning('PhonePe webhook: invalid Authorization header');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $body = $request->json()->all();
        $payload = $body['payload'] ?? [];

        Log::info('PhonePe webhook received', [
            'merchantOrderId' => $payload['merchantOrderId'] ?? null,
            'state' => $payload['state'] ?? null,
        ]);

        if (($payload['state'] ?? null) === 'COMPLETED' && !empty($payload['merchantOrderId'])) {
            $this->recordPaymentIfNeeded($payload['merchantOrderId'], $payload);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Idempotently creates the local Payment record.
     */
    protected function recordPaymentIfNeeded(string $merchantOrderId, array $status): Payment
    {
        $existing = Payment::where('receipt_no', $merchantOrderId)->first();
        if ($existing) {
            return $existing;
        }

        $residentId = cache()->get('phonepe_order_resident_' . $merchantOrderId);
        $resident = $residentId ? Resident::find($residentId) : null;

        $amount = ($status['amount'] ?? 0) / 100;
        $transactionId = $status['paymentDetails'][0]['transactionId'] ?? ('TXN-' . strtoupper(Str::random(10)));

        return Payment::create([
            'resident_id' => $resident?->id,
            'receipt_no' => $merchantOrderId,
            'month' => now()->month,
            'year' => now()->year,
            'rent_amount' => $amount,
            'discount_amount' => 0,
            'fine_amount' => 0,
            'cash_paid_amount' => 0,
            'upi_paid_amount' => $amount,
            'balance_amount' => 0,
            'payment_date' => now(),
            'transaction_id' => $transactionId,
            'status' => 'PAID',
        ]);
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