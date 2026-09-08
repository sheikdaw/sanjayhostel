<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * PhonePe Standard Checkout v2 API client.
 *
 * Endpoints used:
 *  - Auth:           POST /v1/oauth/token
 *  - Create Payment: POST /checkout/v2/pay
 *  - Order Status:   GET  /checkout/v2/order/{merchantOrderId}/status
 *  - Refund:         POST /payments/v2/refund
 *  - Refund Status:  GET  /payments/v2/refund/{merchantRefundId}/status
 *
 * NOTE: PhonePe amounts are always in PAISE (₹1 = 100). This service expects
 * amountInPaise everywhere — convert rupees * 100 before calling it.
 */
class PhonePeService
{
    protected string $env;
    protected string $clientId;
    protected string $clientSecret;
    protected string|int $clientVersion;
    protected string $authBaseUrl;
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->env = strtoupper(config('phonepe.env', 'UAT'));
        $this->clientId = (string) config('phonepe.client_id');
        $this->clientSecret = (string) config('phonepe.client_secret');
        $this->clientVersion = config('phonepe.client_version', 1);

        if ($this->env === 'PRODUCTION') {
            $this->authBaseUrl = 'https://api.phonepe.com/apis/identity-manager';
            $this->apiBaseUrl  = 'https://api.phonepe.com/apis/pg';
        } elseif (in_array($this->env, ['UAT', 'SANDBOX'], true)) {
            $this->authBaseUrl = 'https://api-preprod.phonepe.com/apis/pg-sandbox';
            $this->apiBaseUrl  = 'https://api-preprod.phonepe.com/apis/pg-sandbox';
        } else {
            throw new Exception("Unrecognized PHONEPE_ENV '{$this->env}'. Use UAT, SANDBOX, or PRODUCTION.");
        }
    }

    /**
     * Fetch (and cache) an O-Bearer access token via client_credentials.
     */
    protected function getAccessToken(): string
    {
        $cacheKey = 'phonepe_access_token_' . $this->env;

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $response = Http::asForm()->post($this->authBaseUrl . '/v1/oauth/token', [
                'client_id' => $this->clientId,
                'client_version' => $this->clientVersion,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful()) {
                Log::error('PhonePe OAuth token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('PhonePe authorization failed: ' . $response->body());
            }

            $data = $response->json();

            if (empty($data['access_token'])) {
                throw new Exception('PhonePe authorization response missing access_token');
            }

            return $data['access_token'];
        });
    }

    protected function refreshAccessToken(): string
    {
        Cache::forget('phonepe_access_token_' . $this->env);
        return $this->getAccessToken();
    }

    protected function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'O-Bearer ' . $this->getAccessToken(),
        ];
    }

    /**
     * Create Payment — POST /checkout/v2/pay
     */
    public function createPayment(string $merchantOrderId, int $amountInPaise, string $redirectUrl, string $message = 'Payment'): array
    {
        $payload = [
            'merchantOrderId' => $merchantOrderId,
            'amount' => $amountInPaise,
            'expireAfter' => 1200,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => $message,
                'merchantUrls' => [
                    'redirectUrl' => $redirectUrl,
                ],
            ],
        ];

        $response = Http::withHeaders($this->headers())
            ->post($this->apiBaseUrl . '/checkout/v2/pay', $payload);

        if ($response->status() === 401) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $this->refreshAccessToken(),
            ])->post($this->apiBaseUrl . '/checkout/v2/pay', $payload);
        }

        if (!$response->successful()) {
            Log::error('PhonePe createPayment failed', ['body' => $response->body()]);
            throw new Exception('PhonePe create payment failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Order Status — GET /checkout/v2/order/{merchantOrderId}/status
     */
    public function orderStatus(string $merchantOrderId, bool $details = true): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->apiBaseUrl . "/checkout/v2/order/{$merchantOrderId}/status", [
                'details' => $details ? 'true' : 'false',
            ]);

        if ($response->status() === 401) {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $this->refreshAccessToken(),
            ])->get($this->apiBaseUrl . "/checkout/v2/order/{$merchantOrderId}/status", [
                'details' => $details ? 'true' : 'false',
            ]);
        }

        if (!$response->successful()) {
            Log::error('PhonePe orderStatus failed', ['body' => $response->body()]);
            throw new Exception('PhonePe order status check failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Refund — POST /payments/v2/refund
     */
    public function refund(string $merchantRefundId, string $originalMerchantOrderId, int $amountInPaise): array
    {
        $payload = [
            'merchantRefundId' => $merchantRefundId,
            'originalMerchantOrderId' => $originalMerchantOrderId,
            'amount' => $amountInPaise,
        ];

        $response = Http::withHeaders($this->headers())
            ->post($this->apiBaseUrl . '/payments/v2/refund', $payload);

        if (!$response->successful()) {
            Log::error('PhonePe refund failed', ['body' => $response->body()]);
            throw new Exception('PhonePe refund failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Refund Status — GET /payments/v2/refund/{merchantRefundId}/status
     */
    public function refundStatus(string $merchantRefundId): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->apiBaseUrl . "/payments/v2/refund/{$merchantRefundId}/status");

        if (!$response->successful()) {
            Log::error('PhonePe refundStatus failed', ['body' => $response->body()]);
            throw new Exception('PhonePe refund status check failed: ' . $response->body());
        }

        return $response->json();
    }
}