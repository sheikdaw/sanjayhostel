<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Hostel;
use App\Models\Resident;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AxisBankGateway implements PaymentGatewayInterface
{
    private string $merchantId;
    private string $merchantKey;
    private string $merchantSecret;
    private string $mode;
    private string $baseUrl;
    private string $returnUrl;
    private string $cancelUrl;
    private string $currency;
    private string $webhookSecret;

    public function __construct()
    {
        $this->merchantId     = (string) env('AXIS_MERCHANT_ID', '');
        $this->merchantKey    = (string) env('AXIS_MERCHANT_KEY', '');
        $this->merchantSecret = (string) env('AXIS_MERCHANT_SECRET', '');
        $this->mode           = (string) env('AXIS_MODE', 'sandbox');
        $this->currency       = (string) env('AXIS_CURRENCY', 'INR');
        $this->webhookSecret  = (string) env('AXIS_WEBHOOK_SECRET', '');

        // Base URL: sandbox or live
        $this->baseUrl = $this->mode === 'live'
            ? (string) env('AXIS_BASE_URL', 'https://api.axisbank.com/v1')
            : (string) env('AXIS_SANDBOX_URL', 'https://sandbox.axisbank.com/v1');

        // Return / Cancel URLs (full absolute)
        $this->returnUrl = url(env('AXIS_RETURN_URL', '/pay/callback/axis'));
        $this->cancelUrl = url(env('AXIS_CANCEL_URL', '/pay/cancel/axis'));
    }

    public function getName(): string
    {
        return 'axis';
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantId)
            && !empty($this->merchantKey)
            && !empty($this->merchantSecret)
            && !empty($this->baseUrl);
    }

    /* =========================================================
     |  CREATE ORDER → Axis hosted page URL
     ========================================================= */

    public function createOrder(
        Resident $resident,
        Hostel $hostel,
        float $amount,
        array $meta = []
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success'  => false,
                'mode'     => 'redirect',
                'order_id' => '',
                'amount'   => $amount,
                'message'  => 'Axis Bank not configured. Check .env keys.',
            ];
        }

        $orderId = 'AXIS-' . strtoupper(Str::random(12));

        // ═══════════════════════════════════════════════════════
        //  Build payload — Axis API docs vandha appuram,
        //  field names match pannanum.
        // ═══════════════════════════════════════════════════════
        $payload = [
            'merchant_id'    => $this->merchantId,
            'order_id'       => $orderId,
            'amount'         => number_format($amount, 2, '.', ''),
            'currency'       => $this->currency,
            'customer_name'  => $resident->name,
            'customer_phone' => $resident->phone,
            'customer_email' => $resident->email ?? '',
            'description'    => 'Rent - ' . $resident->name,
            'return_url'     => $this->returnUrl . '?order_id=' . $orderId,
            'cancel_url'     => $this->cancelUrl . '?order_id=' . $orderId,
            'webhook_url'    => route('public.payment.webhook', ['gateway' => 'axis']),
            'meta'           => [
                'resident_id' => (string) $resident->id,
                'hostel_id'   => (string) $hostel->id,
                'order_id'    => $orderId,
            ],
        ];

        // Sign payload
        $payload['signature'] = $this->signPayload($payload);

        // ═══════════════════════════════════════════════════════
        //  TODO: Axis docs vandha appuram, intha try block
        //        uncomment pannanum. Endpoint path matrum
        //        payload field names match pannanum.
        // ═══════════════════════════════════════════════════════
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'X-Merchant-Id' => $this->merchantId,
                'X-Api-Key'     => $this->merchantKey,
            ])
                ->timeout(30)
                ->post($this->baseUrl . '/orders', $payload);

            if (!$response->successful()) {
                Log::error('Axis order create failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [
                    'success'  => false,
                    'mode'     => 'redirect',
                    'order_id' => $orderId,
                    'amount'   => $amount,
                    'message'  => 'Axis Bank error: HTTP ' . $response->status(),
                ];
            }

            $data = $response->json();

            // Axis typically returns:
            //   - payment_url (hosted page with QR + UPI apps)
            //   - qr_code (base64 or URL)
            //   - order_id
            return [
                'success'      => true,
                'mode'         => 'redirect',
                'redirect_url' => $data['payment_url'] ?? $data['redirect_url'] ?? null,
                'qr_code'      => $data['qr_code'] ?? null,
                'order_id'     => $orderId,
                'amount'       => $amount,
                'message'      => 'Axis Bank order created',
            ];

        } catch (\Throwable $e) {
            Log::error('Axis order exception', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            return [
                'success'  => false,
                'mode'     => 'redirect',
                'order_id' => $orderId,
                'amount'   => $amount,
                'message'  => 'Axis connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /* =========================================================
     |  VERIFY WEBHOOK — signature check
     ========================================================= */

    public function verifyWebhook(array $headers, string $rawBody): bool
    {
        if (empty($this->webhookSecret)) {
            return false;
        }

        // Axis sends signature in one of these headers
        $signature = $headers['x-axis-signature'][0]
                  ?? $headers['x-signature'][0]
                  ?? $headers['X-Axis-Signature'][0]
                  ?? $headers['X-Signature'][0]
                  ?? null;

        if (!$signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    /* =========================================================
     |  PARSE WEBHOOK — normalize Axis response
     ========================================================= */

    public function parseWebhook(array $payload): array
    {
        // Axis typically uses: SUCCESS / FAILED / PENDING
        $status = strtoupper($payload['status'] ?? $payload['txn_status'] ?? 'PENDING');

        $orderId   = $payload['order_id']   ?? $payload['merchant_order_id'] ?? '';
        $txnId     = $payload['txn_id']     ?? $payload['transaction_id']    ?? '';
        $amount    = $payload['amount']     ?? $payload['txn_amount']        ?? 0;
        $meta      = $payload['meta']       ?? [];

        return [
            'success'        => $status === 'SUCCESS' || $status === 'CAPTURED',
            'status'         => strtolower($status),
            'order_id'       => $orderId,
            'transaction_id' => $txnId ?: $orderId,
            'amount'         => ((float) $amount) / 100,  // if in paise; adjust per Axis docs
            'resident_id'    => $meta['resident_id'] ?? null,
            'hostel_id'      => $meta['hostel_id'] ?? null,
            'raw'            => $payload,
        ];
    }

    /* =========================================================
     |  Sign payload (HMAC-SHA256)
     ========================================================= */

    private function signPayload(array $payload): string
    {
        // Remove signature before signing
        unset($payload['signature']);

        ksort($payload);
        $string = http_build_query($payload);

        return hash_hmac('sha256', $string, $this->merchantSecret);
    }
}