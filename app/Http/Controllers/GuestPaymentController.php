<?php
// app/Http/Controllers/GuestPaymentController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Resident;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class GuestPaymentController extends Controller
{
    /**
     * Show payment page
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

        // Get all payments
        $allPayments = Payment::where('resident_id', $resident->id)
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Check current month payment
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

        // Calculate amounts
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
        $discountAmount = 0;
        $finalAmount = (float) $totalDue;
        $discountMessage = '';

        if ($pendingPayments->count() == 0 && !$isCurrentMonthPaid && $totalDue > 0) {
            if ($currentDay >= 1 && $currentDay <= 5) {
                $discountAmount = min(250, $rentAmount * 0.10);
                $discountMessage = 'Early payment discount (1st-5th): 10% off up to ₹250';
            } elseif ($currentDay >= 6 && $currentDay <= 10) {
                $discountAmount = min(125, $rentAmount * 0.05);
                $discountMessage = 'Early payment discount (6th-10th): 5% off up to ₹125';
            }
            $discount = $discountAmount;
            $finalAmount = max(0, $totalDue - $discount);
        }

        // Fine calculation
        $fineAmount = 0;
        $fineMessage = '';

        if (!$isCurrentMonthPaid && $currentDay > 10 && $totalDue > 0) {
            $daysLate = $currentDay - 10;
            $fineAmount = $daysLate * 50;
            $fineMessage = "Late fee: ₹50 per day after 10th ({$daysLate} days late)";
        }

        $amountToPay = $finalAmount + $fineAmount;
        $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

        // Get hostel UPI details
        $hostel = Hostel::find($resident->hostel_id);

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
                'discount_amount' => (float) $discount,
                'discount_message' => $discountMessage,
                'discount_applicable' => $discount > 0,
                'fine_amount' => (float) $fineAmount,
                'fine_message' => $fineMessage,
                'final_amount' => (float) $amountToPay,
                'amount_to_pay' => (float) $amountToPay,
                'has_pending' => $pendingPayments->count() > 0,
                'pending_count' => (int) $pendingPayments->count(),
                'reference' => $reference,
                'payment_status' => $isCurrentMonthPaid ? 'PAID' : 'PENDING',
                'is_paid' => $isCurrentMonthPaid,
                'upi_id' => $hostel->upi_id ?? null,
                'upi_payee_name' => $hostel->upi_payee_name ?? $hostel->hostel_name ?? 'Hostel Payment',
                'has_upi' => !empty($hostel->upi_id),
            ]
        ], 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Generate UPI payment link
     */
    public function generateUPI(Request $request)
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

        $resident = Resident::find($request->resident_id);
        $hostel = Hostel::find($resident->hostel_id);

        // Get UPI ID from hostel
        $upiId = $hostel->upi_id ?? null;

        if (!$upiId) {
            return response()->json([
                'success' => false,
                'message' => 'UPI ID not configured for this hostel. Please contact admin.'
            ], 400);
        }

        $amount = (float) $request->amount;
        $reference = $request->reference;
        $payeeName = $hostel->upi_payee_name ?? $hostel->hostel_name ?? 'Hostel Payment';

        // Build UPI URI
        $upiUri = $this->buildUPIUri($upiId, $payeeName, $amount, $reference);

        // Store payment reference in cache for verification
        cache()->put('upi_payment_' . $reference, [
            'resident_id' => $resident->id,
            'amount' => $amount,
            'hostel_id' => $resident->hostel_id,
            'created_at' => now()
        ], now()->addHours(2));

        return response()->json([
            'success' => true,
            'upi_uri' => $upiUri,
            'upi_id' => $upiId,
            'amount' => $amount,
            'reference' => $reference,
            'payee_name' => $payeeName,
            'resident_name' => $resident->name,
            'room_no' => $resident->room->room_no ?? 'N/A',
            'message' => 'Click to pay via UPI'
        ]);
    }

    /**
     * Build UPI URI
     */
    private function buildUPIUri($upiId, $payeeName, $amount, $reference)
    {
        $params = [
            'pa' => $upiId,
            'pn' => $payeeName,
            'am' => number_format($amount, 2),
            'tn' => 'Rent Payment ' . $reference,
            'cu' => 'INR'
        ];

        return 'upi://pay?' . http_build_query($params);
    }

    /**
     * Verify UPI payment (Manual verification or automatic)
     */
    public function verifyUPI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reference = $request->reference;
        $transactionId = $request->transaction_id ?? 'UPI-' . strtoupper(Str::random(10));

        // Check if payment already recorded
        $existingPayment = Payment::where('receipt_no', $reference)->first();
        if ($existingPayment) {
            return response()->json([
                'success' => true,
                'message' => 'Payment already recorded',
                'data' => $existingPayment
            ]);
        }

        // Get payment data from cache
        $paymentData = cache()->get('upi_payment_' . $reference);
        if (!$paymentData) {
            return response()->json([
                'success' => false,
                'message' => 'Payment reference not found or expired'
            ], 404);
        }

        $resident = Resident::find($paymentData['resident_id']);
        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'Resident not found'
            ], 404);
        }

        $paidAmount = $paymentData['amount'];
        $rentAmount = (float) ($resident->rent_amount ?? 0);

        // Determine payment status
        $status = 'PAID';
        $balance = 0;
        if ($paidAmount < $rentAmount) {
            $status = 'PARTIAL';
            $balance = $rentAmount - $paidAmount;
        }

        // Create payment record
        $payment = Payment::create([
            'resident_id' => $resident->id,
            'receipt_no' => $reference,
            'month' => now()->month,
            'year' => now()->year,
            'rent_amount' => $rentAmount,
            'discount_amount' => 0,
            'fine_amount' => 0,
            'cash_paid_amount' => 0,
            'upi_paid_amount' => $paidAmount,
            'balance_amount' => $balance,
            'payment_date' => now(),
            'transaction_id' => $transactionId,
            'status' => $status,
        ]);

        // Clear cache
        cache()->forget('upi_payment_' . $reference);

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully!',
            'data' => $payment
        ]);
    }

    /**
     * Check UPI payment status
     */
    public function checkUPIStatus(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Reference required'
            ], 400);
        }

        // Check in database
        $payment = Payment::where('receipt_no', $reference)->first();
        if ($payment) {
            return response()->json([
                'success' => true,
                'status' => 'COMPLETED',
                'data' => [
                    'status' => $payment->status,
                    'amount' => $payment->upi_paid_amount,
                    'receipt_no' => $payment->receipt_no,
                    'payment_date' => $payment->payment_date->format('d M Y h:i A'),
                    'transaction_id' => $payment->transaction_id,
                ]
            ]);
        }

        // Check in cache
        $cached = cache()->get('upi_payment_' . $reference);
        if ($cached) {
            $createdAt = $cached['created_at'];
            $minutesPassed = now()->diffInMinutes($createdAt);

            if ($minutesPassed > 10) {
                cache()->forget('upi_payment_' . $reference);
                return response()->json([
                    'success' => true,
                    'status' => 'EXPIRED',
                    'message' => 'Payment session expired. Please try again.'
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => 'PENDING',
                'message' => 'Waiting for payment confirmation'
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'NOT_FOUND',
            'message' => 'No payment found for this reference'
        ]);
    }

    /**
     * Browser callback after UPI payment
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        $status = $request->query('status');

        // If status is success from UPI app return
        if ($status === 'success' && $reference) {
            // Verify the payment
            $verifyResult = $this->verifyUPI(new Request([
                'reference' => $reference,
                'transaction_id' => $request->query('transaction_id')
            ]));

            $data = $verifyResult->getData();

            if ($data->success) {
                return view('guest.payment-result', [
                    'success' => true,
                    'message' => 'Payment successful!',
                    'reference' => $reference,
                    'amount' => $data->data->upi_paid_amount ?? 0,
                    'receipt_no' => $data->data->receipt_no ?? $reference,
                    'transaction_id' => $data->data->transaction_id ?? null,
                ]);
            }
        }

        // Check for cancellation
        if ($status === 'cancelled') {
            return view('guest.payment-result', [
                'success' => false,
                'message' => 'Payment was cancelled. You can try again.',
                'reference' => $reference ?? 'N/A',
                'amount' => null,
                'receipt_no' => null,
            ]);
        }

        // If reference exists, check status
        if ($reference) {
            $statusCheck = $this->checkUPIStatus(new Request(['reference' => $reference]));
            $data = $statusCheck->getData();

            if ($data->status === 'COMPLETED') {
                return view('guest.payment-result', [
                    'success' => true,
                    'message' => 'Payment successful!',
                    'reference' => $reference,
                    'amount' => $data->data->amount ?? 0,
                    'receipt_no' => $data->data->receipt_no ?? $reference,
                    'transaction_id' => $data->data->transaction_id ?? null,
                ]);
            }
        }

        // Default - show payment pending or not found
        return view('guest.payment-result', [
            'success' => null,
            'message' => 'Payment is being processed. Please check back in a few minutes.',
            'reference' => $reference ?? 'N/A',
            'amount' => null,
            'receipt_no' => null,
        ]);
    }

    /**
     * Admin: Generate payment link
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
                'upi_id' => $hostel->upi_id,
            ]
        ]);
    }

    // Dummy methods for biometric (keep if needed)
    public function disableBiometricAccess($residentId) { return response()->json(['success' => true]); }
    public function checkBiometricAccess($residentId) { return response()->json(['success' => true]); }
    public function syncBiometricAll() { return response()->json(['success' => true]); }
    public function getDeviceLogs() { return response()->json(['success' => true]); }
    public function getDeviceStatus($deviceId = null) { return response()->json(['success' => true]); }
    public function rebootDevice($deviceId = null) { return response()->json(['success' => true]); }
    public function encodeId($hostelId) {
        return response()->json(['success' => true, 'encoded' => Crypt::encryptString($hostelId)]);
    }
    public function decodeId($encodedId) {
        try {
            return response()->json(['success' => true, 'decoded' => Crypt::decryptString($encodedId)]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid encoded ID']);
        }
    }
}
