<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AxisBankService
{
    protected $merchantId;
    protected $merchantKey;
    protected $merchantSecret;
    protected $baseUrl;
    protected $mode;
    protected $returnUrl;
    protected $cancelUrl;
    protected $currency;

    public function __construct()
    {
        $this->merchantId = config('axisbank.merchant_id');
        $this->merchantKey = config('axisbank.merchant_key');
        $this->merchantSecret = config('axisbank.merchant_secret');
        $this->mode = config('axisbank.mode', 'sandbox');
        $this->baseUrl = $this->mode === 'sandbox' 
            ? config('axisbank.sandbox_url') 
            : config('axisbank.base_url');
        $this->returnUrl = config('axisbank.return_url');
        $this->cancelUrl = config('axisbank.cancel_url');
        $this->currency = config('axisbank.currency', 'INR');
    }

    /**
     * Generate HMAC SHA256 signature for request
     */
    protected function generateSignature($data, $secret = null)
    {
        $secret = $secret ?? $this->merchantSecret;
        ksort($data);
        $string = http_build_query($data, '', '&');
        return hash_hmac('sha256', $string, $secret);
    }

    /**
     * Generate Unique Transaction ID
     */
    public function generateTransactionId()
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    /**
     * Get API headers for authentication
     */
    protected function getHeaders($additionalHeaders = [])
    {
        return array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Merchant-ID' => $this->merchantId,
            'X-API-Key' => $this->merchantKey,
        ], $additionalHeaders);
    }

    /**
     * Create a payment order with Axis Bank
     */
    public function createOrder(array $orderData)
    {
        try {
            $transactionId = $this->generateTransactionId();
            $amount = (float) $orderData['amount'];
            $receipt = $orderData['receipt'] ?? $transactionId;

            // Prepare request payload
            $payload = [
                'merchant_id' => $this->merchantId,
                'transaction_id' => $transactionId,
                'order_id' => $receipt,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => $this->currency,
                'return_url' => url($this->returnUrl),
                'cancel_url' => url($this->cancelUrl),
                'reference_id' => $orderData['reference_id'] ?? $receipt,
                'description' => $orderData['description'] ?? 'Rent Payment',
                'customer_name' => $orderData['customer_name'] ?? 'Guest',
                'customer_email' => $orderData['customer_email'] ?? '',
                'customer_phone' => $orderData['customer_phone'] ?? '',
            ];

            // Add notes if provided
            if (!empty($orderData['notes'])) {
                $payload['notes'] = $orderData['notes'];
            }

            // Generate signature
            $signature = $this->generateSignature($payload);
            $payload['signature'] = $signature;

            // Log request
            Log::info('Axis Bank: Creating order', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'payload' => $payload
            ]);

            // Make API call
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl . '/payment/order/create', $payload);

            // Log response
            Log::info('Axis Bank: Order creation response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store transaction details in cache for callback verification
                Cache::put('axis_order_' . $receipt, [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'payload' => $payload,
                    'signature' => $signature,
                ], now()->addHours(2));

                return [
                    'success' => true,
                    'order_id' => $data['order_id'] ?? $transactionId,
                    'transaction_id' => $data['transaction_id'] ?? $transactionId,
                    'payment_url' => $data['payment_url'] ?? null,
                    'amount' => $amount,
                    'currency' => $this->currency,
                    'merchant_id' => $this->merchantId,
                    'signature' => $signature,
                    'receipt' => $receipt,
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to create order',
                'error' => $response->json(),
            ];

        } catch (Exception $e) {
            Log::error('Axis Bank: Order creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Payment service error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature
     */
    public function verifyPaymentSignature(array $payload)
    {
        try {
            // Get the signature from payload
            $signature = $payload['signature'] ?? null;
            if (!$signature) {
                Log::warning('Axis Bank: No signature in payload');
                return false;
            }

            // Remove signature before verification
            $payloadCopy = $payload;
            unset($payloadCopy['signature']);

            // Verify signature
            $expectedSignature = $this->generateSignature($payloadCopy);
            $isValid = hash_equals($expectedSignature, $signature);

            Log::info('Axis Bank: Signature verification', [
                'is_valid' => $isValid,
                'expected' => $expectedSignature,
                'received' => $signature
            ]);

            return $isValid;

        } catch (Exception $e) {
            Log::error('Axis Bank: Signature verification error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Fetch payment status from Axis Bank
     */
    public function fetchPayment($transactionId)
    {
        try {
            $payload = [
                'merchant_id' => $this->merchantId,
                'transaction_id' => $transactionId,
            ];

            $signature = $this->generateSignature($payload);
            $payload['signature'] = $signature;

            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl . '/payment/status', $payload);

            Log::info('Axis Bank: Payment status fetch', [
                'transaction_id' => $transactionId,
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'PENDING',
                    'amount' => $data['amount'] ?? 0,
                    'currency' => $data['currency'] ?? $this->currency,
                    'transaction_id' => $data['transaction_id'] ?? $transactionId,
                    'order_id' => $data['order_id'] ?? null,
                    'payment_date' => $data['payment_date'] ?? null,
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to fetch payment status',
            ];

        } catch (Exception $e) {
            Log::error('Axis Bank: Payment status fetch error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error fetching payment status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund
     */
    public function processRefund(array $refundData)
    {
        try {
            $payload = [
                'merchant_id' => $this->merchantId,
                'transaction_id' => $refundData['transaction_id'],
                'refund_amount' => number_format($refundData['amount'], 2, '.', ''),
                'refund_reason' => $refundData['reason'] ?? 'Customer request',
                'refund_reference_id' => $refundData['refund_reference_id'] ?? 'REF-' . date('Ymd') . '-' . uniqid(),
            ];

            $signature = $this->generateSignature($payload);
            $payload['signature'] = $signature;

            $response = Http::withHeaders($this->getHeaders())
                ->post($this->baseUrl . '/payment/refund', $payload);

            Log::info('Axis Bank: Refund request', [
                'payload' => $payload,
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'refund_id' => $data['refund_id'] ?? null,
                    'status' => $data['status'] ?? 'PROCESSING',
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to process refund',
            ];

        } catch (Exception $e) {
            Log::error('Axis Bank: Refund error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Refund error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate payment button/form
     */
    public function generatePaymentForm(array $orderData)
    {
        $html = '<form id="axisBankPaymentForm" method="POST" action="' . $this->baseUrl . '/payment/redirect">';
        $html .= '<input type="hidden" name="merchant_id" value="' . $this->merchantId . '">';
        $html .= '<input type="hidden" name="order_id" value="' . $orderData['order_id'] . '">';
        $html .= '<input type="hidden" name="transaction_id" value="' . $orderData['transaction_id'] . '">';
        $html .= '<input type="hidden" name="amount" value="' . $orderData['amount'] . '">';
        $html .= '<input type="hidden" name="currency" value="' . $this->currency . '">';
        $html .= '<input type="hidden" name="return_url" value="' . url($this->returnUrl) . '">';
        $html .= '<input type="hidden" name="cancel_url" value="' . url($this->cancelUrl) . '">';
        $html .= '<input type="hidden" name="signature" value="' . $orderData['signature'] . '">';
        $html .= '<button type="submit" class="btn-pay">Pay Now</button>';
        $html .= '</form>';
        
        return $html;
    }
}