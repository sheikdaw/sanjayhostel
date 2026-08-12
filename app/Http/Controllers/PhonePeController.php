<?php

namespace App\Http\Controllers;

use App\Services\PhonePeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class PhonePeController extends Controller
{
    protected PhonePeService $phonePe;

    public function __construct(PhonePeService $phonePe)
    {
        $this->phonePe = $phonePe;
    }

    /**
     * Quick sanity check that credentials + connectivity are working.
     * GET /phonepe/test
     */
    public function test()
    {
        try {
            $merchantOrderId = 'TEST-' . time();

            $result = $this->phonePe->createPayment(
                $merchantOrderId,
                100, // ₹1.00 in paise
                config('phonepe.redirect_url') . '?merchant_order_id=' . $merchantOrderId,
                'Test Payment'
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generic entry point — POST /phonepe/pay
     */
    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reference' => 'nullable|string',
            'resident_id' => 'nullable|exists:residents,id',
        ]);

        try {
            $merchantOrderId = $request->reference ?: ('ORDER-' . strtoupper(Str::random(12)));
            $amountInPaise = (int) round($request->amount * 100);
            $redirectUrl = config('phonepe.redirect_url') . '?merchant_order_id=' . $merchantOrderId;

            // If resident_id is provided, add it to redirect URL
            if ($request->resident_id) {
                $redirectUrl .= '&resident_id=' . $request->resident_id;
            }

            $result = $this->phonePe->createPayment(
                $merchantOrderId,
                $amountInPaise,
                $redirectUrl,
                'Payment - ' . ($request->resident_id ? 'Resident #' . $request->resident_id : 'Guest')
            );

            // Extract redirect URL from response
            $redirectUrl = $result['paymentFlow']['merchantUrls']['redirectUrl'] ?? null;
            $orderId = $result['orderId'] ?? $result['merchantOrderId'] ?? null;

            return response()->json([
                'success' => true,
                'merchant_order_id' => $merchantOrderId,
                'redirect_url' => $redirectUrl,
                'order_id' => $orderId,
                'state' => $result['state'] ?? 'PENDING',
            ]);

        } catch (Exception $e) {
            Log::error('PhonePe pay error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle PhonePe callback after payment
     * GET /phonepe/callback
     */
    public function callback(Request $request)
    {
        $merchantOrderId = $request->input('merchant_order_id');
        $residentId = $request->input('resident_id');

        Log::info('PhonePe callback received', [
            'merchant_order_id' => $merchantOrderId,
            'resident_id' => $residentId,
            'all_params' => $request->all(),
        ]);

        if (!$merchantOrderId) {
            return redirect()->route('guest.payment.index')
                ->with('error', 'Payment reference not found');
        }

        try {
            // Get payment status from PhonePe
            $status = $this->phonePe->orderStatus($merchantOrderId, true);

            Log::info('PhonePe callback status check', [
                'merchant_order_id' => $merchantOrderId,
                'status' => $status,
            ]);

            $state = $status['state'] ?? 'PENDING';
            $transactionId = $status['transactionId'] ?? null;
            $amount = $status['amount'] ?? 0;

            if ($state === 'COMPLETED' || $state === 'SUCCESS') {
                // Find the payment in our system
                $payment = \App\Models\Payment::where('receipt_no', $merchantOrderId)
                    ->orWhere('transaction_id', $transactionId)
                    ->first();

                if ($payment) {
                    $payment->status = 'PAID';
                    $payment->payment_date = now();
                    if ($transactionId) {
                        $payment->transaction_id = $transactionId;
                    }
                    $payment->save();
                }

                // Redirect to success page with encoded hostel ID if available
                $encodedHostelId = $request->input('encodedHostelId');
                $hostelId = $request->input('hostel_id');

                if (!$encodedHostelId && $hostelId) {
                    try {
                        $encodedHostelId = \Illuminate\Support\Facades\Crypt::encryptString($hostelId);
                    } catch (\Exception $e) {
                        // Ignore
                    }
                }

                return redirect()->route('guest.payment.callback', [
                    'reference' => $merchantOrderId,
                    'amount' => $amount > 0 ? $amount / 100 : 0,
                    'resident_id' => $residentId,
                    'encodedHostelId' => $encodedHostelId,
                ]);
            }

            if ($state === 'PENDING' || $state === 'INITIATED') {
                // Still pending - show pending page
                return view('guest.success', [
                    'success' => null,
                    'reference' => $merchantOrderId,
                    'amount' => $amount > 0 ? $amount / 100 : 0,
                    'message' => 'Your payment is being processed. Please wait...',
                    'encodedHostelId' => $request->input('encodedHostelId'),
                ]);
            }

            // Failed or unknown
            return redirect()->route('guest.payment.index')
                ->with('error', 'Payment failed. Please try again.');

        } catch (Exception $e) {
            Log::error('PhonePe callback error: ' . $e->getMessage(), [
                'merchant_order_id' => $merchantOrderId,
            ]);

            return redirect()->route('guest.payment.index')
                ->with('error', 'Error processing payment callback: ' . $e->getMessage());
        }
    }

    /**
     * Order Status — GET /phonepe/status/{merchantOrderId}
     */
    public function status(string $merchantOrderId)
    {
        try {
            $status = $this->phonePe->orderStatus($merchantOrderId, true);

            // Extract state from response
            $state = $status['state'] ?? 'UNKNOWN';
            
            // Map state to consistent values
            if (in_array($state, ['COMPLETED', 'SUCCESS'])) {
                $state = 'COMPLETED';
            } elseif (in_array($state, ['FAILED', 'ERROR'])) {
                $state = 'FAILED';
            } elseif (in_array($state, ['PENDING', 'INITIATED'])) {
                $state = 'PENDING';
            }

            return response()->json([
                'success' => true,
                'state' => $state,
                'data' => $status,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refund — POST /phonepe/refund
     * Body: { merchant_order_id, amount }
     */
    public function refund(Request $request)
    {
        $request->validate([
            'merchant_order_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $merchantRefundId = 'REFUND-' . strtoupper(Str::random(10));

            $result = $this->phonePe->refund(
                $merchantRefundId,
                $request->merchant_order_id,
                (int) round($request->amount * 100)
            );

            return response()->json([
                'success' => true,
                'merchant_refund_id' => $merchantRefundId,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refund Status — GET /phonepe/refund-status/{merchantRefundId}
     */
    public function refundStatus(string $merchantRefundId)
    {
        try {
            $result = $this->phonePe->refundStatus($merchantRefundId);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}