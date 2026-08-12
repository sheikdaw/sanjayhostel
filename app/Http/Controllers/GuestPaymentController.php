<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use App\Services\PhonePeService;
use App\Services\MockEbioServerService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class GuestPaymentController extends Controller
{
    protected PhonePeService $phonePe;
    protected MockEbioServerService $biometric;

    public function __construct(PhonePeService $phonePe, MockEbioServerService $biometric)
    {
        $this->phonePe = $phonePe;
        $this->biometric = $biometric;
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

        $currentPayment = Payment::where('resident_id', $resident->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        $pendingPayments = Payment::where('resident_id', $resident->id)
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalDue = $pendingPayments->sum('balance_amount');
        $discount = 0;
        $discountType = null;
        $rentAmount = (float) ($resident->rent_amount ?? 0);
        $finalAmount = (float) $totalDue;

        // Calculate discount if applicable
        if ($totalDue == 0 && !$currentPayment) {
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
        } else if ($totalDue > 0 && !$currentPayment) {
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
                'total_due' => (float) $finalAmount,
                'discount' => (float) $discount,
                'discount_type' => $discountType,
                'pending_count' => (int) $pendingPayments->count(),
                'reference' => $reference,
                'has_pending' => $pendingPayments->count() > 0,
                'is_paid' => $totalDue == 0 && $currentPayment && $currentPayment->status === 'PAID'
            ]
        ]);
    }

    /**
     * Generate QR code for payment - Shows PhonePe QR and UPI options
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
        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Resident not found'
            ], 404);
        }

        $roomNo = $resident->room->room_no ?? 'N/A';
        $residentName = $resident->name;

        // Remember which resident this order belongs to
        cache()->put('phonepe_order_resident_' . $reference, $residentId, now()->addHours(2));

        try {
            // Try PhonePe first
            $result = $this->phonePe->createPayment(
                $reference,
                (int) round($amount * 100), // rupees -> paise
                route('guest.payment.callback', ['merchant_order_id' => $reference]),
                "Rent - {$residentName} - Room {$roomNo}"
            );

            // Generate UPI QR as fallback
            $upiId = env('UPI_ID', 'merchant@upi');
            $merchantName = env('MERCHANT_NAME', 'Hostel Payment');
            $upiUrl = "upi://pay?pa=" . $upiId .
                      "&pn=" . urlencode($merchantName) .
                      "&am=" . number_format($amount, 2, '.', '') .
                      "&cu=INR" .
                      "&tn=" . urlencode("Rent-{$residentName}-Room{$roomNo}") .
                      "&refid=" . $reference;

            return response()->json([
                'success' => true,
                'redirect_url' => $result['redirectUrl'] ?? null,
                'upi_url' => $upiUrl,
                'qr_code' => $this->generateQRCode($upiUrl),
                'order_id' => $result['orderId'] ?? null,
                'state' => $result['state'] ?? 'PENDING',
                'reference' => $reference,
                'amount' => $amount,
                'resident_name' => $residentName,
                'room_no' => $roomNo,
                'upi_id' => $upiId,
                'merchant_name' => $merchantName,
                'payment_methods' => [
                    'phonepe' => true,
                    'upi' => true
                ]
            ]);

        } catch (Exception $e) {
            Log::error('PhonePe payment creation failed: ' . $e->getMessage(), [
                'reference' => $reference,
                'resident_id' => $residentId
            ]);

            // Fallback to UPI only
            $upiId = env('UPI_ID', 'merchant@upi');
            $merchantName = env('MERCHANT_NAME', 'Hostel Payment');
            $upiUrl = "upi://pay?pa=" . $upiId .
                      "&pn=" . urlencode($merchantName) .
                      "&am=" . number_format($amount, 2, '.', '') .
                      "&cu=INR" .
                      "&tn=" . urlencode("Rent-{$residentName}-Room{$roomNo}") .
                      "&refid=" . $reference;

            return response()->json([
                'success' => true,
                'redirect_url' => $upiUrl,
                'upi_url' => $upiUrl,
                'qr_code' => $this->generateQRCode($upiUrl),
                'reference' => $reference,
                'amount' => $amount,
                'resident_name' => $residentName,
                'room_no' => $roomNo,
                'upi_id' => $upiId,
                'merchant_name' => $merchantName,
                'payment_methods' => [
                    'phonepe' => false,
                    'upi' => true
                ],
                'fallback' => true,
                'message' => 'PhonePe is temporarily unavailable. Please use UPI.'
            ]);
        }
    }

    /**
     * Generate QR Code as base64 image
     */
    private function generateQRCode($upiUrl)
    {
        try {
            // Use Simple QR Code library
            $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                ->format('png')
                ->generate($upiUrl);
            
            return base64_encode($qrCode);
        } catch (Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            return null;
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

            // Enable biometric access after payment
            $this->enableBiometricAccess($payment->resident_id);

            return view('guest.payment-result', [
                'success' => true,
                'message' => 'Payment successful! Your biometric access has been enabled.',
                'reference' => $merchantOrderId,
                'amount' => ($status['amount'] ?? 0) / 100,
                'receipt_no' => $payment->receipt_no ?? $merchantOrderId,
                'biometric_enabled' => true
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
            
            // Enable biometric access
            if ($payment && $payment->resident_id) {
                $this->enableBiometricAccess($payment->resident_id);
            }

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
     * PhonePe Webhook (S2S callback).
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
        $event = $body['event'] ?? null;
        $payload = $body['payload'] ?? [];

        Log::info('PhonePe webhook received', [
            'event' => $event,
            'merchantOrderId' => $payload['merchantOrderId'] ?? null,
            'state' => $payload['state'] ?? null,
        ]);

        if (($payload['state'] ?? null) === 'COMPLETED' && !empty($payload['merchantOrderId'])) {
            $payment = $this->recordPaymentIfNeeded($payload['merchantOrderId'], $payload);
            
            // Enable biometric access via webhook
            if ($payment && $payment->resident_id) {
                $this->enableBiometricAccess($payment->resident_id);
            }
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

        $payment = Payment::create([
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

        return $payment;
    }

    /**
     * ============================================================
     * BIOMETRIC INTEGRATION METHODS (Using MockEbioServerService)
     * ============================================================
     */

    /**
     * Enable biometric access for a resident after successful payment
     */
    protected function enableBiometricAccess($residentId)
    {
        if (!$residentId) {
            return;
        }

        try {
            $resident = Resident::with('room')->find($residentId);
            if (!$resident) {
                Log::warning('Resident not found for biometric access', ['resident_id' => $residentId]);
                return;
            }

            // Generate employee code from resident ID
            $employeeCode = 'RES_' . str_pad($resident->id, 6, '0', STR_PAD_LEFT);
            
            // First sync/update employee in biometric system
            $syncResult = $this->biometric->updateEmployee([
                'employee_code' => $employeeCode,
                'name' => $resident->name,
                'phone' => $resident->phone,
                'email' => $resident->email,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'hostel_id' => $resident->hostel_id,
                'access_enabled' => true
            ]);

            if ($syncResult['success']) {
                // Then enable access
                $enableResult = $this->biometric->enableEmployee($employeeCode);
                
                Log::info('Biometric access enabled for resident', [
                    'resident_id' => $residentId,
                    'employee_code' => $employeeCode,
                    'name' => $resident->name,
                    'room' => $resident->room->room_no ?? 'N/A',
                    'sync_result' => $syncResult,
                    'enable_result' => $enableResult
                ]);
            } else {
                Log::warning('Failed to sync resident with biometric system', [
                    'resident_id' => $residentId,
                    'error' => $syncResult['message'] ?? 'Unknown error'
                ]);
            }

        } catch (Exception $e) {
            Log::error('Biometric access enable error: ' . $e->getMessage(), [
                'resident_id' => $residentId
            ]);
        }
    }

    /**
     * Disable biometric access for a resident
     */
    public function disableBiometricAccess($residentId)
    {
        try {
            $resident = Resident::find($residentId);
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            $employeeCode = 'RES_' . str_pad($resident->id, 6, '0', STR_PAD_LEFT);
            $result = $this->biometric->disableEmployee($employeeCode);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'Access disabled',
                'employee_code' => $employeeCode
            ]);

        } catch (Exception $e) {
            Log::error('Biometric access disable error: ' . $e->getMessage(), [
                'resident_id' => $residentId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check biometric access status for a resident
     */
    public function checkBiometricAccess($residentId)
    {
        try {
            $resident = Resident::find($residentId);
            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident not found'
                ], 404);
            }

            $employeeCode = 'RES_' . str_pad($resident->id, 6, '0', STR_PAD_LEFT);
            $employee = $this->biometric->getEmployee($employeeCode);

            return response()->json([
                'success' => true,
                'data' => [
                    'resident_id' => $residentId,
                    'name' => $resident->name,
                    'employee_code' => $employeeCode,
                    'access_enabled' => $employee['access_enabled'] ?? false,
                    'synced_at' => $employee['synced_at'] ?? null,
                    'enabled_at' => $employee['enabled_at'] ?? null,
                    'room_no' => $resident->room->room_no ?? 'N/A'
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all residents with biometric system
     */
    public function syncBiometricAll()
    {
        try {
            $residents = Resident::where('status', 'ACTIVE')->get();
            $results = [];

            foreach ($residents as $resident) {
                $employeeCode = 'RES_' . str_pad($resident->id, 6, '0', STR_PAD_LEFT);
                $result = $this->biometric->updateEmployee([
                    'employee_code' => $employeeCode,
                    'name' => $resident->name,
                    'phone' => $resident->phone,
                    'email' => $resident->email,
                    'room_no' => $resident->room->room_no ?? 'N/A',
                    'hostel_id' => $resident->hostel_id,
                    'access_enabled' => true
                ]);

                $results[] = [
                    'resident_id' => $resident->id,
                    'name' => $resident->name,
                    'employee_code' => $employeeCode,
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'Synced'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => count($results),
                    'synced' => collect($results)->where('success', true)->count(),
                    'failed' => collect($results)->where('success', false)->count(),
                    'results' => $results
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device logs (attendance)
     */
    public function getDeviceLogs(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        try {
            $result = $this->biometric->getDeviceLogs($date);

            return response()->json([
                'success' => $result['success'] ?? false,
                'data' => $result['data'] ?? null,
                'count' => $result['count'] ?? 0,
                'date' => $result['date'] ?? $date
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device status
     */
    public function getDeviceStatus($deviceId = 'DEV_001')
    {
        try {
            $result = $this->biometric->getDeviceLastPing($deviceId);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get device status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reboot device
     */
    public function rebootDevice($deviceId = 'DEV_001')
    {
        try {
            $result = $this->biometric->rebootDevice($deviceId);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'Device rebooted',
                'data' => $result
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reboot device: ' . $e->getMessage()
            ], 500);
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