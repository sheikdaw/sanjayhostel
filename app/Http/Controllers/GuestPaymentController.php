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
            ->first();

        if (!$resident) {
            return response()->json([
                'success' => false,
                'message' => 'No resident found with this mobile number in this hostel.'
            ], 404);
        }

        $pendingPayments = Payment::where('resident_id', $resident->id)
            ->whereIn('status', ['PENDING', 'PARTIAL'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $totalDue = $pendingPayments->sum('balance_amount');

        if ($totalDue == 0) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $currentPayment = Payment::where('resident_id', $resident->id)
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->first();

            $totalDue = $currentPayment ? $currentPayment->rent_amount : ($resident->rent_amount ?? 0);
        }

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

        $amount = $request->amount;
        $reference = $request->reference;
        $residentId = $request->resident_id;

        $resident = Resident::find($residentId);
        $roomNo = $resident && $resident->room ? ($resident->room->room_no ?? 'N/A') : 'N/A';
        $residentName = $resident ? $resident->name : 'Resident';

        // Remember which resident this order belongs to so the callback/
        // status/webhook handlers can create the Payment record once
        // PhonePe confirms COMPLETED.
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
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Browser lands here after PhonePe checkout (success, failure, or
     * abandonment). We ALWAYS re-verify with the Order Status API.
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

        // Fast path: already recorded locally (e.g. by the webhook)
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
            'state' => $state, // PENDING or FAILED
        ]);
    }

    /**
     * PhonePe Webhook (S2S callback) — the fully automatic path. PhonePe
     * calls this the instant a payment reaches a terminal state, no matter
     * which UPI app the guest used to pay. No polling, no redirect
     * dependency — the database updates itself.
     *
     * POST /guest/payment/webhook
     * NOTE: must be exempt from CSRF verification (see setup notes).
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

        // Rely on payload.state (root-level), not just the event name —
        // this is PhonePe's own recommendation.
        if (($payload['state'] ?? null) === 'COMPLETED' && !empty($payload['merchantOrderId'])) {
            $this->recordPaymentIfNeeded($payload['merchantOrderId'], $payload);
        }

        // Must return 2xx quickly or PhonePe will retry the webhook.
        return response()->json(['status' => 'ok']);
    }

    /**
     * Idempotently creates the local Payment record once PhonePe has
     * confirmed COMPLETED. Safe to call multiple times for the same order
     * (webhook + callback + polling may all race to call this).
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