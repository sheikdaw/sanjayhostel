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

    // If no pending payments and current month is paid, everything is clear
    if ($totalDue == 0 && $isCurrentMonthPaid) {
        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $resident->id,
                'name' => $resident->name,
                'email' => $resident->email,
                'phone' => $resident->phone,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'rent_amount' => $resident->rent_amount ?? 0,
                'total_due' => 0,
                'pending_count' => 0,
                'reference' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
                'has_pending' => false,
                'is_paid' => true,
                'payment_status' => 'PAID',
                'message' => 'All payments are up to date. No dues pending.',
                'current_month_status' => 'PAID',
                'pending_payments' => []
            ]
        ]);
    }

    // If no pending payments but current month not paid (no record exists)
    if ($totalDue == 0 && !$currentPayment) {
        // Current month rent is due
        $rentAmount = $resident->rent_amount ?? 0;
        $totalDue = $rentAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'resident_id' => $resident->id,
                'name' => $resident->name,
                'email' => $resident->email,
                'phone' => $resident->phone,
                'room_no' => $resident->room->room_no ?? 'N/A',
                'rent_amount' => $rentAmount,
                'total_due' => $totalDue,
                'pending_count' => 1,
                'reference' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8)),
                'has_pending' => true,
                'is_paid' => false,
                'payment_status' => 'PENDING',
                'message' => 'Current month rent is due.',
                'current_month_status' => 'NOT_CREATED',
                'pending_payments' => [
                    [
                        'month' => $currentMonth,
                        'year' => $currentYear,
                        'amount' => $rentAmount,
                        'status' => 'PENDING'
                    ]
                ]
            ]
        ]);
    }

    // If there are pending payments (including partial payments)
    $pendingDetails = [];
    foreach ($pendingPayments as $payment) {
        $monthName = date('F', mktime(0, 0, 0, $payment->month, 1));
        $pendingDetails[] = [
            'month' => $monthName . ' ' . $payment->year,
            'amount' => $payment->balance_amount,
            'status' => $payment->status,
            'total_amount' => $payment->rent_amount,
            'paid_amount' => $payment->upi_paid_amount + $payment->cash_paid_amount
        ];
    }

    // Generate payment reference
    $reference = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(8));

    return response()->json([
        'success' => true,
        'data' => [
            'resident_id' => $resident->id,
            'name' => $resident->name,
            'email' => $resident->email,
            'phone' => $resident->phone,
            'room_no' => $resident->room->room_no ?? 'N/A',
            'rent_amount' => $resident->rent_amount ?? 0,
            'total_due' => $totalDue,
            'pending_count' => $pendingPayments->count(),
            'reference' => $reference,
            'has_pending' => $pendingPayments->count() > 0,
            'is_paid' => false,
            'payment_status' => $pendingPayments->count() > 0 ? 'PENDING' : 'PAID',
            'message' => $pendingPayments->count() > 0 ? 'You have pending payments.' : 'All payments are up to date.',
            'current_month_status' => $currentMonthStatus,
            'pending_payments' => $pendingDetails
        ]
    ]);
}

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

    $amount = $request->amount;
    $reference = $request->reference;
    $residentId = $request->resident_id;

    // Get resident for UPI reference
    $resident = Resident::find($residentId);

    // Get room number safely
    $roomNo = 'N/A';
    if ($resident && $resident->room) {
        $roomNo = $resident->room->room_no ?? 'N/A';
    }

    // UPI payment URL format
    $upiId = env('UPI_ID', 'merchant@upi');
    $merchantName = env('MERCHANT_NAME', 'Hostel Payment');

    // Add resident name and room to transaction note
    $residentName = $resident ? $resident->name : 'Resident';
    $transactionNote = "Rent-" . $residentName . "-Room" . $roomNo;

    $upiUrl = "upi://pay?pa=" . $upiId .
              "&pn=" . urlencode($merchantName) .
              "&am=" . $amount .
              "&cu=INR" .
              "&tn=" . urlencode($transactionNote) .
              "&refid=" . $reference;

    // Generate QR code as SVG
    $qrCode = QrCode::size(300)->generate($upiUrl);

    return response()->json([
        'success' => true,
        'qr_code' => $qrCode,
        'upi_url' => $upiUrl,
        'amount' => $amount,
        'reference' => $reference,
        'upi_id' => $upiId,
        'merchant_name' => $merchantName,
        'resident_name' => $residentName,
        'room_no' => $roomNo
    ]);
}
    /**
     * Handle payment success callback
     */
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

        $paymentAmount = $request->amount;
        $remainingAmount = $paymentAmount;

        // If there are pending payments, apply payment to oldest first
        foreach ($pendingPayments as $pending) {
            if ($remainingAmount <= 0) break;

            $balanceDue = $pending->balance_amount;
            if ($balanceDue <= 0) continue;

            if ($remainingAmount >= $balanceDue) {
                // Fully pay this pending payment
                $pending->upi_paid_amount += $balanceDue;
                $pending->balance_amount = 0;
                $pending->status = 'PAID';
                $pending->payment_date = now();
                $pending->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                $pending->save();

                $remainingAmount -= $balanceDue;
            } else {
                // Partially pay this pending payment
                $pending->upi_paid_amount += $remainingAmount;
                $pending->balance_amount = $balanceDue - $remainingAmount;
                $pending->status = 'PARTIAL';
                $pending->payment_date = now();
                $pending->transaction_id = $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10));
                $pending->save();

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
                    $currentPayment->upi_paid_amount += $remainingAmount;
                    $currentPayment->balance_amount = max(0, $currentPayment->rent_amount - $currentPayment->upi_paid_amount - $currentPayment->cash_paid_amount);
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
        $payment->load('resident');

        return response()->json([ 
            'success' => true,
            'message' => 'Payment recorded successfully!',
            'data' => [
                'payment' => $payment,
                'resident' => $resident,
                'receipt_no' => $payment->receipt_no,
                'amount' => $payment->upi_paid_amount,
                'payment_date' => $payment->payment_date->format('d M Y h:i A'),
                'resident_name' => $resident->name,
                'payment_status' => $payment->status,
                'remaining_balance' => $payment->balance_amount ?? 0
            ]
        ]);

    } catch (\Exception $e) {
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

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $payment->status,
                'amount' => $payment->upi_paid_amount,
                'receipt_no' => $payment->receipt_no,
                'payment_date' => $payment->payment_date->format('d M Y h:i A'),
                'resident' => $payment->resident->name ?? 'N/A'
            ]
        ]);
    }

    /**
     * Generate encoded hostel link
     * This is used by admin to generate secure payment links
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

        // Also generate QR code for the link itself (optional)
        $qrLink = QrCode::size(200)->generate($url);

        return response()->json([
            'success' => true,
            'data' => [
                'hostel_id' => $hostelId,
                'hostel_name' => $hostel->hostel_name,
                'hostel_code' => $hostel->hostel_code ?? 'HOSTEL',
                'encoded_id' => $encodedId,
                'payment_link' => $url,
                'qr_code_link' => $qrLink
            ]
        ]);
    }

    /**
     * Helper to encode a hostel ID (for manual use)
     */
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to encode ID: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to decode an encoded hostel ID (for debugging)
     */
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decode ID: ' . $e->getMessage()
            ], 500);
        }
    }
}
