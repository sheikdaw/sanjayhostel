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

        // Check if there are pending payments
        $pendingPayments = Payment::where('resident_id', $resident->id)
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalDue = $pendingPayments->sum('balance_amount');

        // If no pending payments, get current month's rent
        if ($totalDue == 0) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            if ($currentPayment) {
                $totalDue = $currentPayment->rent_amount;
            } else {
                // If no payment record, use resident's rent amount
                $totalDue = $resident->rent_amount ?? 0;
            }
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
                'has_pending' => $pendingPayments->count() > 0
            ]
        ]);
    }

    /**
     * Generate QR code for UPI payment - FIXED VERSION
     */
    /**
 * Generate QR code for UPI payment
 * This method accepts GET parameters
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
        'merchant_name' => $merchantName
    ]);
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

            // Create payment record
            $payment = Payment::create([
                'resident_id' => $resident->id,
                'receipt_no' => $request->reference,
                'month' => now()->month,
                'year' => now()->year,
                'rent_amount' => $request->amount,
                'discount_amount' => 0,
                'fine_amount' => 0,
                'cash_paid_amount' => 0,
                'upi_paid_amount' => $request->amount,
                'balance_amount' => 0,
                'payment_date' => now(),
                'transaction_id' => $request->transaction_id ?? 'TXN-' . strtoupper(Str::random(10)),
                'status' => 'PAID'
            ]);

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
                    'resident_name' => $resident->name
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
