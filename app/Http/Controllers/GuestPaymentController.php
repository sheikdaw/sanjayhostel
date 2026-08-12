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
     * Display the guest payment page
     */
    public function index(Request $request, $encodedHostelId = null)
    {
        $hostelId = null;
        $decodedId = null;

        if ($encodedHostelId) {
            try {
                $decodedId = Crypt::decryptString($encodedHostelId);
                $hostelId = $decodedId;
            } catch (\Exception $e) {
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

        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        $pendingPayments = Payment::where('resident_id', $resident->id)
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

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

        $totalDue = $pendingPayments->sum('balance_amount');
        $discount = 0;
        $discountType = null;
        $rentAmount = (float) ($resident->rent_amount ?? 0);
        $finalAmount = (float) $totalDue;

        if ($totalDue == 0 && !$currentPayment && !$isCurrentMonthPaid) {
            $totalDue = $rentAmount;
            $finalAmount = $rentAmount;
            
            if ($currentDay <= 10) {
                if ($currentDay <= 5) {
                    $discount = min(250, $rentAmount * 0.10);
                    $discountType = 'early_discount_250';
                } else {
                    $discount = min(125, $rentAmount * 0.05);
                    $discountType = 'early_discount_125';
                }
                $finalAmount = max(0, $totalDue - $discount);
            }
        } else if ($totalDue > 0 && !$currentPayment && !$isCurrentMonthPaid) {
            $totalDue += $rentAmount;
            $finalAmount = $totalDue;
            
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

        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

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

            $resident = Resident::with('room')->find($residentId);

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            $roomNo = $resident->room->room_no ?? 'N/A';
            $residentName = $resident->name;

            // Try PhonePe first
            $phonePeEnabled = env('PHONEPE_ENABLED', false);
            
            if ($phonePeEnabled) {
                try {
                    $result = $this->createPhonePePayment($reference, $amount, $resident);
                    
                    if ($result && isset($result['redirectUrl'])) {
                        return response()->json([
                            'success' => true,
                            'redirect_url' => $result['redirectUrl'],
                            'reference' => $reference,
                            'amount' => (float) $amount,
                            'resident_name' => $residentName,
                            'room_no' => $roomNo,
                            'payment_method' => 'PHONEPE'
                        ], 200, [], JSON_NUMERIC_CHECK);
                    }
                } catch (\Exception $e) {
                    Log::warning('PhonePe payment creation failed: ' . $e->getMessage());
                }
            }

            // Fallback to UPI QR Code
            $upiId = env('UPI_ID', 'merchant@upi');
            $merchantName = env('MERCHANT_NAME', 'Hostel Payment');

            $transactionNote = "Rent-{$residentName}-Room{$roomNo}";
            $upiUrl = "upi://pay?pa=" . $upiId .
                      "&pn=" . urlencode($merchantName) .
                      "&am=" . number_format($amount, 2, '.', '') .
                      "&cu=INR" .
                      "&tn=" . urlencode($transactionNote) .
                      "&refid=" . $reference;

            $qrCode = QrCode::size(300)->generate($upiUrl);

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
                'upi_url' => $upiUrl,
                'redirect_url' => $upiUrl,
                'amount' => (float) $amount,
                'reference' => $reference,
                'upi_id' => $upiId,
                'merchant_name' => $merchantName,
                'resident_name' => $residentName,
                'room_no' => $roomNo,
                'payment_method' => 'UPI_FALLBACK'
            ], 200, [], JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {
            Log::error('QR Generation Error: ' . $e->getMessage(), [
                'reference' => $request->reference,
                'resident_id' => $request->resident_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Create PhonePe payment
     */
    private function createPhonePePayment($reference, $amount, $resident)
    {
        try {
            $merchantId = config('phonepe.client_id', env('PHONEPE_CLIENT_ID'));
            $saltKey = config('phonepe.salt_key', env('PHONEPE_SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399'));
            $saltIndex = config('phonepe.salt_index', env('PHONEPE_SALT_INDEX', 1));
            
            $environment = config('phonepe.env', env('PHONEPE_ENV', 'UAT'));
            $baseUrl = $environment === 'PROD' 
                ? 'https://api.phonepe.com/apis/hermes' 
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
            
            $redirectUrl = config('phonepe.redirect_url', env('PHONEPE_REDIRECT_URL'));

            $roomNo = $resident->room->room_no ?? 'N/A';
            $residentName = $resident->name;
            $mobile = $resident->phone ?? '';

            $payload = [
                'merchantId' => $merchantId,
                'merchantTransactionId' => $reference,
                'merchantUserId' => 'M-' . $resident->id,
                'amount' => (int) round($amount * 100),
                'redirectUrl' => $redirectUrl . '?reference=' . $reference . '&resident_id=' . $resident->id,
                'redirectMode' => 'REDIRECT',
                'callbackUrl' => route('guest.payment.webhook'),
                'mobileNumber' => $mobile,
                'paymentInstrument' => [
                    'type' => 'PAY_PAGE'
                ],
                'transactionNote' => "Rent - {$residentName} - Room {$roomNo}",
            ];

            $payloadBase64 = base64_encode(json_encode($payload));
            $checksum = hash('sha256', $payloadBase64 . '/pg/v1/pay' . $saltKey) . '###' . $saltIndex;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'X-CLIENT-ID' => $merchantId,
                'X-CLIENT-VERSION' => config('phonepe.client_version', 1)
            ])->post($baseUrl . '/pg/v1/pay', [
                'request' => $payloadBase64
            ]);

            $result = $response->json();

            Log::info('PhonePe Payment Response', [
                'reference' => $reference,
                'response' => $result
            ]);

            if (isset($result['success']) && $result['success'] === true) {
                return [
                    'redirectUrl' => $result['data']['instrumentResponse']['redirectInfo']['url'] ?? 
                                   $result['data']['redirectUrl'] ?? null,
                    'orderId' => $result['data']['orderId'] ?? null,
                    'state' => $result['data']['state'] ?? 'PENDING'
                ];
            }

            Log::error('PhonePe payment failed', [
                'reference' => $reference,
                'response' => $result
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('PhonePe API error: ' . $e->getMessage(), [
                'reference' => $reference
            ]);
            return null;
        }
    }

    /**
     * Handle PhonePe callback
     */
    public function callback(Request $request)
    {
        $reference = $request->input('merchant_order_id') ?? $request->input('reference');
        
        if (!$reference) {
            $reference = session('payment_reference');
        }

        if (!$reference) {
            return redirect()->route('guest.payment.index')
                ->with('error', 'Payment reference not found');
        }

        // Get resident_id from request
        $residentId = $request->input('resident_id');
        
        // Get payment details
        $payment = Payment::where('receipt_no', $reference)->first();

        // Check PhonePe status
        $status = $this->checkPhonePeStatus($reference);
        
        if ($status && isset($status['state']) && $status['state'] === 'COMPLETED') {
            if ($payment) {
                $payment->status = 'PAID';
                $payment->payment_date = now();
                $payment->save();
            } else {
                // Create payment record if it doesn't exist
                if ($residentId) {
                    $resident = Resident::find($residentId);
                    if ($resident) {
                        $currentMonth = now()->month;
                        $currentYear = now()->year;
                        
                        $payment = Payment::create([
                            'resident_id' => $resident->id,
                            'receipt_no' => $reference,
                            'month' => $currentMonth,
                            'year' => $currentYear,
                            'rent_amount' => $request->input('amount', 0),
                            'discount_amount' => 0,
                            'fine_amount' => 0,
                            'cash_paid_amount' => 0,
                            'upi_paid_amount' => $request->input('amount', 0),
                            'balance_amount' => 0,
                            'payment_date' => now(),
                            'transaction_id' => $status['transactionId'] ?? 'TXN-' . strtoupper(Str::random(10)),
                            'status' => 'PAID'
                        ]);
                    }
                }
            }

            return view('guest.success', [
                'success' => true,
                'reference' => $reference,
                'amount' => $payment->upi_paid_amount ?? $request->input('amount', 0),
                'receipt_no' => $payment->receipt_no ?? $reference,
                'encodedHostelId' => $request->input('encodedHostelId')
            ]);
        }

        return view('guest.success', [
            'success' => false,
            'reference' => $reference,
            'message' => 'Payment is still being processed. Please check back later.',
            'encodedHostelId' => $request->input('encodedHostelId')
        ]);
    }

    /**
     * Check PhonePe payment status
     */
    private function checkPhonePeStatus($reference)
    {
        try {
            $merchantId = config('phonepe.client_id', env('PHONEPE_CLIENT_ID'));
            $saltKey = config('phonepe.salt_key', env('PHONEPE_SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399'));
            $saltIndex = config('phonepe.salt_index', env('PHONEPE_SALT_INDEX', 1));
            
            $environment = config('phonepe.env', env('PHONEPE_ENV', 'UAT'));
            $baseUrl = $environment === 'PROD' 
                ? 'https://api.phonepe.com/apis/hermes' 
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox';

            $checksum = hash('sha256', '/pg/v1/status/' . $merchantId . '/' . $reference . $saltKey) . '###' . $saltIndex;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
                'X-MERCHANT-ID' => $merchantId
            ])->get($baseUrl . '/pg/v1/status/' . $merchantId . '/' . $reference);

            $result = $response->json();

            Log::info('PhonePe Status Check', [
                'reference' => $reference,
                'response' => $result
            ]);

            if (isset($result['success']) && $result['success'] === true) {
                return [
                    'state' => $result['data']['state'] ?? 'PENDING',
                    'transactionId' => $result['data']['transactionId'] ?? null,
                    'amount' => $result['data']['amount'] ?? 0
                ];
            }

            return ['state' => 'PENDING'];

        } catch (\Exception $e) {
            Log::error('PhonePe status check error: ' . $e->getMessage(), [
                'reference' => $reference
            ]);
            return ['state' => 'PENDING'];
        }
    }

    /**
     * Get payment status by reference (AJAX polling)
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
        
        // Check local database first
        $payment = Payment::where('receipt_no', $reference)
            ->with('resident')
            ->first();

        if ($payment && $payment->status === 'PAID') {
            return response()->json([
                'success' => true,
                'state' => 'COMPLETED',
                'data' => [
                    'status' => $payment->status,
                    'amount' => (float) $payment->upi_paid_amount,
                    'receipt_no' => $payment->receipt_no,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('d M Y h:i A') : null,
                    'resident' => $payment->resident->name ?? 'N/A'
                ]
            ], 200, [], JSON_NUMERIC_CHECK);
        }

        // Check PhonePe status
        $status = $this->checkPhonePeStatus($reference);
        
        if ($status && $status['state'] === 'COMPLETED') {
            // Update payment if exists
            if ($payment) {
                $payment->status = 'PAID';
                $payment->payment_date = now();
                $payment->save();
            }
            
            return response()->json([
                'success' => true,
                'state' => 'COMPLETED',
                'data' => [
                    'status' => 'PAID',
                    'amount' => (float) ($payment->upi_paid_amount ?? $status['amount'] ?? 0),
                    'receipt_no' => $payment->receipt_no ?? $reference,
                    'payment_date' => now()->format('d M Y h:i A'),
                    'resident' => $payment->resident->name ?? 'N/A'
                ]
            ], 200, [], JSON_NUMERIC_CHECK);
        }

        return response()->json([
            'success' => true,
            'state' => $status['state'] ?? 'PENDING',
            'data' => [
                'status' => 'PENDING',
                'message' => 'Payment is being processed'
            ]
        ]);
    }

    /**
     * Handle successful payment (legacy/alternative endpoint)
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
                    $pending->upi_paid_amount = (float) $pending->upi_paid_amount + $balanceDue;
                    $pending->balance_amount = 0;
                    $pending->status = 'PAID';
                    $pending->payment_date = now();
                    $pending->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                    $pending->save();
                    $payment = $pending;
                    $remainingAmount -= $balanceDue;
                } else {
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

                $currentPayment = Payment::where('resident_id', $resident->id)
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->first();

                if ($currentPayment && $currentPayment->status === 'PAID') {
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
                    if ($currentPayment) {
                        $currentPayment->upi_paid_amount = (float) $currentPayment->upi_paid_amount + $remainingAmount;
                        $currentPayment->balance_amount = max(0, (float) $currentPayment->rent_amount - (float) $currentPayment->upi_paid_amount - (float) $currentPayment->cash_paid_amount);
                        $currentPayment->status = $currentPayment->balance_amount > 0 ? 'PARTIAL' : 'PAID';
                        $currentPayment->payment_date = now();
                        $currentPayment->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                        $currentPayment->save();
                        $payment = $currentPayment;
                    } else {
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
            $reference = $payload['merchantTransactionId'] ?? null;

            // Find payment by transaction ID or reference
            $payment = Payment::where('transaction_id', $transactionId)
                ->orWhere('receipt_no', $reference)
                ->first();

            if (!$payment) {
                Log::warning('Payment not found for webhook', [
                    'transaction_id' => $transactionId,
                    'reference' => $reference
                ]);
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