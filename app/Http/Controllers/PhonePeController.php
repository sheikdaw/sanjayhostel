<?php

namespace App\Http\Controllers;

use App\Services\PhonePeService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;

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
        ]);

        try {
            $merchantOrderId = $request->reference ?: ('ORDER-' . strtoupper(Str::random(12)));
            $amountInPaise = (int) round($request->amount * 100);

            $result = $this->phonePe->createPayment(
                $merchantOrderId,
                $amountInPaise,
                config('phonepe.redirect_url') . '?merchant_order_id=' . $merchantOrderId,
                'Payment'
            );

            return response()->json([
                'success' => true,
                'merchant_order_id' => $merchantOrderId,
                'redirect_url' => $result['redirectUrl'] ?? null,
                'order_id' => $result['orderId'] ?? null,
                'state' => $result['state'] ?? null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Order Status — GET /phonepe/status/{merchantOrderId}
     */
    public function status(string $merchantOrderId)
    {
        try {
            $status = $this->phonePe->orderStatus($merchantOrderId, true);

            return response()->json([
                'success' => true,
                'state' => $status['state'] ?? 'UNKNOWN',
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