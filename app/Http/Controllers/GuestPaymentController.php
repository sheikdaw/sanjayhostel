<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuestPaymentController extends Controller
{
    /**
     * Display the guest payment page with QR code
     */
    public function index(Request $request, $encodedHostelId = null)
    {
        // Decode the hostel ID
        $hostelId = null;
        $decodedId = null;

        if ($encodedHostelId) {
            try {
                // Try to decrypt the ID
                $decodedId = Crypt::decryptString($encodedHostelId);
                $hostelId = $decodedId;
            } catch (\Exception $e) {
                // If decryption fails, try to use as plain ID (for testing)
                if (is_numeric($encodedHostelId)) {
                    $hostelId = $encodedHostelId;
                } else {
                    abort(404, 'Invalid payment link');
                }
            }
        }

        // Get hostel details
        $hostel = null;
        if ($hostelId) {
            $hostel = Hostel::with('roomTypes')->find($hostelId);
            if (!$hostel) {
                abort(404, 'Hostel not found');
            }
        }

        // Generate unique transaction reference
        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

        // Re-encode the hostel ID for the response
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
     * Get resident details by mobile number
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

        // Find resident by phone number
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

        // Get current month and year
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentDay = now()->day;

        // Check if current month's payment exists
        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        // Get all pending payments
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

        // Calculate discount if applicable
        $discount = 0;
        $discountType = null;
        $rentAmount = (float) ($resident->rent_amount ?? 0);
        $finalAmount = (float) $totalDue;

        // If no pending payments and current month not paid, calculate for current month
        if ($totalDue == 0 && !$currentPayment && !$isCurrentMonthPaid) {
            $totalDue = $rentAmount;
            $finalAmount = $rentAmount;
            
            // Apply early payment discount if applicable (1st-10th of month)
            if ($currentDay <= 10) {
                if ($currentDay <= 5) {
                    $discount = min(250, $rentAmount * 0.10); // 10% up to ₹250
                    $discountType = 'early_discount_250';
                } else {
                    $discount = min(125, $rentAmount * 0.05); // 5% up to ₹125
                    $discountType = 'early_discount_125';
                }
                $finalAmount = max(0, $totalDue - $discount);
            }
        } 
        // If there are pending payments, check if current month is also due
        else if ($totalDue > 0 && !$currentPayment && !$isCurrentMonthPaid) {
            // Add current month rent to pending
            $totalDue += $rentAmount;
            $finalAmount = $totalDue;
            
            // Apply discount only if no pending payments from previous months
            if ($pendingPayments->count() == 0 && $currentDay <= 10) {
                if ($currentDay <= 5) {
                    $discount = min(250, $rentAmount * 0.10);
                    $discountType = 'early_discount_250';
                } else {
                    $discount = min(125, $rentAmount * 0.05);
                    $discountType = 'early_discount_125';
                }
                $finalAmount = max(0, $totalDue - $discount);
            }
        }

        // If there are pending payments
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

        // Prepare response data with proper numeric types
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
            'pending_count' => (int) $pendingPayments->count(),
            'reference' => $reference,
            'has_pending' => $pendingPayments->count() > 0,
            'is_paid' => $totalDue == 0 && $isCurrentMonthPaid,
            'payment_status' => $totalDue > 0 ? 'PENDING' : 'PAID',
            'message' => $totalDue > 0 ? 'You have pending payments.' : 'All payments are up to date.',
            'current_month_status' => $currentMonthStatus,
            'pending_payments' => $pendingDetails
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Generate QR code for payment
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

        try {
            $amount = (float) $request->amount;
            $reference = $request->reference;
            $residentId = (int) $request->resident_id;

            // Get resident for UPI reference
            $resident = Resident::with('room')->find($residentId);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            // Get room number safely
            $roomNo = $resident->room->room_no ?? 'N/A';
            $residentName = $resident->name;

            // UPI payment details
            $upiId = env('UPI_ID', 'merchant@upi');
            $merchantName = env('MERCHANT_NAME', 'Hostel Payment');

            // Create UPI payment URL for QR code
            $transactionNote = "Rent-{$residentName}-Room{$roomNo}";
            $upiUrl = "upi://pay?pa=" . $upiId .
                      "&pn=" . urlencode($merchantName) .
                      "&am=" . number_format($amount, 2, '.', '') .
                      "&cu=INR" .
                      "&tn=" . urlencode($transactionNote) .
                      "&refid=" . $reference;

            // Generate QR code as SVG
            $qrCode = QrCode::size(300)->generate($upiUrl);

            // If you have PhonePe integration
            $phonePeUrl = null;
            if (env('PHONEPE_ENABLED', false)) {
                try {
                    // Call PhonePe payment
                    $result = $this->createPhonePePayment($reference, $amount, $resident);
                    if ($result && isset($result['redirectUrl'])) {
                        $phonePeUrl = $result['redirectUrl'];
                    }
                } catch (\Exception $e) {
                    Log::warning('PhonePe payment creation failed: ' . $e->getMessage());
                }
            }

            // If PhonePe is not enabled or failed, use UPI
            if (!$phonePeUrl) {
                $phonePeUrl = route('guest.payment.phonepe.redirect', [
                    'reference' => $reference,
                    'amount' => $amount,
                    'resident_id' => $residentId
                ]);
            }

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
                'upi_url' => $upiUrl,
                'redirect_url' => $phonePeUrl ?? $upiUrl,
                'amount' => (float) $amount,
                'reference' => $reference,
                'upi_id' => $upiId,
                'merchant_name' => $merchantName,
                'resident_name' => $residentName,
                'room_no' => $roomNo
            ], 200, [], JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            Log::error('QR Generation Error: ' . $e->getMessage(), [
                'reference' => $request->reference,
                'resident_id' => $request->resident_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Create PhonePe payment
     */
    private function createPhonePePayment($reference, $amount, $resident)
    {
        $merchantId = env('PHONEPE_MERCHANT_ID', 'MERCHANTUAT');
        $saltKey = env('PHONEPE_SALT_KEY', 'your_salt_key');
        $saltIndex = env('PHONEPE_SALT_INDEX', '1');
        $baseUrl = env('PHONEPE_BASE_URL', 'https://api.phonepe.com/apis/hermes');
        
        $payload = [
            'merchantId' => $merchantId,
            'merchantOrderId' => $reference,
            'amount' => (int) round($amount * 100),
            'paymentType' => 'UPI',
            'redirectUrl' => route('guest.payment.callback'),
            'callbackUrl' => route('guest.payment.webhook'),
            'merchantName' => env('MERCHANT_NAME', 'Hostel Payment'),
            'transactionNote' => "Rent - {$resident->name} - Room {$resident->room->room_no ?? 'N/A'}"
        ];

        $payloadBase64 = base64_encode(json_encode($payload));
        $checksum = hash('sha256', $payloadBase64 . '/pg/v1/pay' . $saltKey) . '###' . $saltIndex;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum
            ])->post($baseUrl . '/pg/v1/pay', [
                'request' => $payloadBase64
            ]);

            $result = $response->json();

            if ($result && isset($result['code']) && $result['code'] === 'PAYMENT_INITIATED') {
                return [
                    'redirectUrl' => $result['data']['redirectUrl'] ?? null,
                    'orderId' => $result['data']['orderId'] ?? null
                ];
            }

            Log::error('PhonePe payment initiation failed', ['response' => $result]);
            return null;

        } catch (\Exception $e) {
            Log::error('PhonePe API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle payment success callback
     */
    public function success(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'resident_id' => 'required|exists:residents,id',
            'transaction_id' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if payment already processed
            $existingPayment = Payment::where('receipt_no', $request->reference)
                ->orWhere('transaction_id', $request->transaction_id)
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment already processed',
                    'data' => $existingPayment
                ]);
            }

            $resident = Resident::find($request->resident_id);
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            // Check for existing pending payments
            $pendingPayments = Payment::where('resident_id', $resident->id)
                ->whereIn('status', ['PENDING', 'PARTIAL'])
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $paymentAmount = (float) $request->amount;
            $remainingAmount = $paymentAmount;
            $payment = null;

            // If there are pending payments, apply payment to oldest first
            foreach ($pendingPayments as $pending) {
                if ($remainingAmount <= 0) break;

                $balanceDue = (float) $pending->balance_amount;
                if ($balanceDue <= 0) continue;

                if ($remainingAmount >= $balanceDue) {
                    // Fully pay this pending payment
                    $pending->upi_paid_amount = (float) $pending->upi_paid_amount + $balanceDue;
                    $pending->balance_amount = 0;
                    $pending->status = 'PAID';
                    $pending->payment_date = now();
                    $pending->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                    $pending->save();
                    $payment = $pending;

                    $remainingAmount -= $balanceDue;
                } else {
                    // Partially pay this pending payment
                    $pending->upi_paid_amount = (float) $pending->upi_paid_amount + $remainingAmount;
                    $pending->balance_amount = $balanceDue - $remainingAmount;
                    $pending->status = 'PARTIAL';
                    $pending->payment_date = now();
                    $pending->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                    $pending->save();
                    $payment = $pending;

                    $remainingAmount = 0;
                }
            }

            // If there's remaining amount and no pending payments, create new payment
            if ($remainingAmount > 0) {
                $currentMonth = now()->month;
                $currentYear = now()->year;

                // Check if current month's payment exists
                $currentPayment = Payment::where('resident_id', $resident->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->first();

                if ($currentPayment && $currentPayment->status === 'PAID') {
                    // Current month is already paid, create a future/advance payment
                    $nextMonth = now()->addMonth()->month;
                    $nextYear = now()->addMonth()->year;

                    $payment = Payment::create([
                        'resident_id' => $resident->id,
                        'receipt_no' => $request->reference,
                        'month' => $nextMonth,
                        'year' => $nextYear,
                        'rent_amount' => $remainingAmount,
                        'discount_amount' => 0,
                        'fine_amount' => 0,
                        'cash_paid_amount' => 0,
                        'upi_paid_amount' => $remainingAmount,
                        'balance_amount' => 0,
                        'payment_date' => now(),
                        'transaction_id' => $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10)),
                        'status' => 'PAID'
                    ]);
                } else {
                    // Create new payment for current month or update existing
                    if ($currentPayment) {
                        // Update existing payment
                        $currentPayment->upi_paid_amount = (float) $currentPayment->upi_paid_amount + $remainingAmount;
                        $currentPayment->balance_amount = max(0, (float) $currentPayment->rent_amount - (float) $currentPayment->upi_paid_amount - (float) $currentPayment->cash_paid_amount);
                        $currentPayment->status = $currentPayment->balance_amount > 0 ? 'PARTIAL' : 'PAID';
                        $currentPayment->payment_date = now();
                        $currentPayment->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                        $currentPayment->save();
                        $payment = $currentPayment;
                    } else {
                        // Create new payment
                        $payment = Payment::create([
                            'resident_id' => $resident->id,
                            'receipt_no' => $request->reference,
                            'month' => $currentMonth,
                            'year' => $currentYear,
                            'rent_amount' => $remainingAmount,
                            'discount_amount' => 0,
                            'fine_amount' => 0,
                            'cash_paid_amount' => 0,
                            'upi_paid_amount' => $remainingAmount,
                            'balance_amount' => 0,
                            'payment_date' => now(),
                            'transaction_id' => $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10)),
                            'status' => 'PAID'
                        ]);
                    }
                }
            }

            // Load resident relationship
            if ($payment) {
                $payment->load('resident');
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully!',
                'data' => [
                    'payment' => $payment,
                    'resident' => $resident,
                    'receipt_no' => $payment->receipt_no ?? $request->reference,
                    'amount' => (float) ($payment->upi_paid_amount ?? $paymentAmount),
                    'payment_date' => $payment->payment_date->format('d M Y h:i A'),
                    'resident_name' => $resident->name,
                    'payment_status' => $payment->status ?? 'PAID',
                    'remaining_balance' => (float) ($payment->balance_amount ?? 0)
                ]
            ], 200, [], JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'reference' => $request->reference,
                'resident_id' => $request->resident_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment status by reference
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

        $payment = Payment::where('receipt_no', $request->reference)
            ->with('resident')
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Check PhonePe status if transaction_id exists
        $state = 'COMPLETED';
        if ($payment->transaction_id && env('PHONEPE_ENABLED', false)) {
            try {
                $phonePeStatus = $this->getPhonePeStatus($payment->transaction_id);
                if ($phonePeStatus && isset($phonePeStatus['state'])) {
                    $state = $phonePeStatus['state'];
                }
            } catch (\Exception $e) {
                Log::warning('PhonePe status check failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'state' => $state,
            'data' => [
                'status' => $payment->status,
                'amount' => (float) $payment->upi_paid_amount,
                'receipt_no' => $payment->receipt_no,
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('d M Y h:i A') : null,
                'resident' => $payment->resident->name ?? 'N/A'
            ]
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Get PhonePe payment status
     */
    private function getPhonePeStatus($transactionId)
    {
        $merchantId = env('PHONEPE_MERCHANT_ID', 'MERCHANTUAT');
        $saltKey = env('PHONEPE_SALT_KEY', 'your_salt_key');
        $saltIndex = env('PHONEPE_SALT_INDEX', '1');
        $baseUrl = env('PHONEPE_BASE_URL', 'https://api.phonepe.com/apis/hermes');

        $checksum = hash('sha256', '/pg/v1/status/' . $merchantId . '/' . $transactionId . $saltKey) . '###' . $saltIndex;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'X-MERCHANT-ID' => $merchantId
            ])->get($baseUrl . '/pg/v1/status/' . $merchantId . '/' . $transactionId);

            $result = $response->json();

            if ($result && isset($result['code']) && $result['code'] === 'PAYMENT_SUCCESS') {
                return [
                    'state' => 'COMPLETED',
                    'data' => $result['data'] ?? []
                ];
            }

            return ['state' => 'PENDING'];

        } catch (\Exception $e) {
            Log::error('PhonePe status API error: ' . $e->getMessage());
            return ['state' => 'PENDING'];
        }
    }

    /**
     * Handle PhonePe callback
     */
    public function callback(Request $request)
    {
        $reference = $request->input('merchant_order_id') ?? $request->input('reference');
        
        if (!$reference) {
            // Try to get from session
            $reference = session('payment_reference');
        }

        if (!$reference) {
            return redirect()->route('guest.payment.index')
                ->with('error', 'Payment reference not found');
        }

        // Get payment details
        $payment = Payment::where('receipt_no', $reference)->first();

        if ($payment && $payment->status === 'PAID') {
            // Payment already completed
            return view('guest.success', [
                'success' => true,
                'reference' => $reference,
                'amount' => $payment->upi_paid_amount,
                'receipt_no' => $payment->receipt_no
            ]);
        }

        // Check PhonePe status
        if ($payment && $payment->transaction_id && env('PHONEPE_ENABLED', false)) {
            $status = $this->getPhonePeStatus($payment->transaction_id);
            
            if ($status['state'] === 'COMPLETED') {
                // Update payment status
                $payment->status = 'PAID';
                $payment->payment_date = now();
                $payment->save();

                return view('guest.success', [
                    'success' => true,
                    'reference' => $reference,
                    'amount' => $payment->upi_paid_amount,
                    'receipt_no' => $payment->receipt_no
                ]);
            }
        }

        // Still pending or failed
        return view('guest.success', [
            'success' => false,
            'reference' => $reference,
            'message' => 'Payment is still being processed. Please check back later.'
        ]);
    }

    /**
     * Handle PhonePe webhook
     */
    public function webhook(Request $request)
    {
        Log::info('PhonePe webhook received', $request->all());

        try {
            $payload = $request->all();
            
            if (!isset($payload['transactionId'])) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $transactionId = $payload['transactionId'];
            $status = $payload['state'] ?? 'PENDING';

            // Find payment by transaction ID
            $payment = Payment::where('transaction_id', $transactionId)->first();

            if (!$payment) {
                Log::warning('Payment not found for webhook', ['transaction_id' => $transactionId]);
                return response()->json(['error' => 'Payment not found'], 404);
            }

            if ($status === 'COMPLETED') {
                $payment->status = 'PAID';
                $payment->payment_date = now();
                $payment->save();

                Log::info('Payment updated via webhook', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId
                ]);
            } elseif ($status === 'FAILED') {
                $payment->status = 'FAILED';
                $payment->save();

                Log::warning('Payment failed via webhook', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId
                ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Redirect to PhonePe payment
     */
    public function redirectToPhonePe(Request $request)
    {
        $reference = $request->input('reference');
        $amount = $request->input('amount');
        $residentId = $request->input('resident_id');

        if (!$reference || !$amount || !$residentId) {
            return redirect()->route('guest.payment.index')
                ->with('error', 'Invalid payment request');
        }

        $resident = Resident::find($residentId);
        if (!$resident) {
            return redirect()->route('guest.payment.index')
                ->with('error', 'Resident not found');
        }

        // Create PhonePe payment
        $result = $this->createPhonePePayment($reference, $amount, $resident);

        if ($result && isset($result['redirectUrl'])) {
            // Store reference in session for callback
            session(['payment_reference' => $reference]);
            return redirect($result['redirectUrl']);
        }

        return redirect()->route('guest.payment.index')
            ->with('error', 'Failed to initiate payment. Please try again.');
    }

    /**
     * Generate encoded hostel link
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

        $qrLink = QrCode::size(200)->generate($url);

        return response()->json([
            'success' => true,
            'data' => [
                'hostel_id' => (int) $hostelId,
                'hostel_name' => $hostel->hostel_name,
                'hostel_code' => $hostel->hostel_code ?? 'HOSTEL',
                'encoded_id' => $encodedId,
                'payment_link' => $url,
                'qr_code_link' => $qrLink
            ]
        ]);
    }

    /**
     * Helper to encode a hostel ID
     */
    public function encodeId($hostelId)
    {
        try {
            $encoded = Crypt::encryptString($hostelId);
            return response()->json([
                'success' => true,
                'data' => [
                    'original_id' => (int) $hostelId,
                    'encoded_id' => $encoded,
                    'url' => url('/guest/payment/' . $encoded)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to encode ID: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to decode an encoded hostel ID
     */
    public function decodeId($encodedId)
    {
        try {
            $decoded = Crypt::decryptString($encodedId);
            return response()->json([
                'success' => true,
                'data' => [
                    'encoded_id' => $encodedId,
                    'decoded_id' => (int) $decoded
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decode ID: ' . $e->getMessage()
            ], 500);
        }
    }
}