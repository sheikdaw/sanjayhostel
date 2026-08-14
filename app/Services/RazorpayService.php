<?php
// app/Services/RazorpayService.php

namespace App\Services;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Exception;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected Api $api;
    protected string $keyId;
    protected string $keySecret;

    public function __construct()
    {
        $this->keyId = config('razorpay.key_id');
        $this->keySecret = config('razorpay.key_secret');
        $this->api = new Api($this->keyId, $this->keySecret);
    }

    /**
     * Create a new payment order
     */
    public function createOrder(array $data): array
    {
        try {
            $order = $this->api->order->create([
                'receipt' => $data['receipt'] ?? uniqid(),
                'amount' => $data['amount'] * 100, // Convert to paise
                'currency' => $data['currency'] ?? 'INR',
                'notes' => $data['notes'] ?? [],
            ]);

            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'] / 100,
                'currency' => $order['currency'],
                'receipt' => $order['receipt'],
                'status' => $order['status'],
                'key_id' => $this->keyId,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            throw new Exception('Failed to create payment order: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature(array $payload): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature($payload);
            return true;
        } catch (SignatureVerificationError $e) {
            Log::error('Razorpay signature verification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch payment details
     */
    public function fetchPayment(string $paymentId): array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);
            return $payment->toArray();
        } catch (Exception $e) {
            Log::error('Razorpay fetch payment failed: ' . $e->getMessage());
            throw new Exception('Failed to fetch payment details: ' . $e->getMessage());
        }
    }

    /**
     * Fetch order details
     */
    public function fetchOrder(string $orderId): array
    {
        try {
            $order = $this->api->order->fetch($orderId);
            return $order->toArray();
        } catch (Exception $e) {
            Log::error('Razorpay fetch order failed: ' . $e->getMessage());
            throw new Exception('Failed to fetch order details: ' . $e->getMessage());
        }
    }

    /**
     * Create a refund
     */
    public function createRefund(string $paymentId, float $amount, string $reason = ''): array
    {
        try {
            $refund = $this->api->payment->fetch($paymentId)->refund([
                'amount' => $amount * 100,
                'speed' => 'normal',
                'notes' => ['reason' => $reason],
            ]);
            return $refund->toArray();
        } catch (Exception $e) {
            Log::error('Razorpay refund failed: ' . $e->getMessage());
            throw new Exception('Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Fetch refund status
     */
    public function fetchRefund(string $refundId): array
    {
        try {
            $refund = $this->api->refund->fetch($refundId);
            return $refund->toArray();
        } catch (Exception $e) {
            Log::error('Razorpay fetch refund failed: ' . $e->getMessage());
            throw new Exception('Failed to fetch refund status: ' . $e->getMessage());
        }
    }
}