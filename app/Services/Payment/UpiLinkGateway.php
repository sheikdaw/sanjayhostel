<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Hostel;
use App\Models\Resident;
use Illuminate\Support\Str;

class UpiLinkGateway implements PaymentGatewayInterface
{
    public function getName(): string
    {
        return 'upi_link';
    }

    public function isConfigured(): bool
    {
        return true; // Always available
    }

    public function createOrder(
        Resident $resident,
        Hostel $hostel,
        float $amount,
        array $meta = []
    ): array {
        $upiId        = $hostel->upi_id;
        $upiPayeeName = $hostel->upi_payee_name ?? $hostel->hostel_name;

        if (!$upiId) {
            return [
                'success' => false,
                'mode'    => 'upi_link',
                'order_id' => '',
                'amount'  => $amount,
                'message' => 'UPI not configured for this hostel.',
            ];
        }

        // Strip full URL if upi_id stored as full string
        $rawUpiId = $upiId;
        if (strpos($upiId, 'pa=') !== false) {
            preg_match('/pa=([^&]+)/', $upiId, $m);
            if (isset($m[1])) {
                $rawUpiId = urldecode($m[1]);
            }
        }

        $orderId = 'UPI-' . strtoupper(Str::random(12));

        $upiLink = 'upi://pay?' . http_build_query([
            'pa' => $rawUpiId,
            'pn' => $upiPayeeName,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => 'Rent - ' . $resident->name . ' - ' . $orderId,
            'tr' => $orderId,
        ]);

        return [
            'success'   => true,
            'mode'      => 'upi_link',
            'upi_link'  => $upiLink,
            'order_id'  => $orderId,
            'amount'    => $amount,
            'message'   => 'UPI deep-link ready',
        ];
    }

    public function verifyWebhook(array $headers, string $rawBody): bool
    {
        return false; // No webhook for UPI link
    }

    public function parseWebhook(array $payload): array
    {
        return [
            'success' => false,
            'status'  => 'pending',
            'order_id' => '',
            'transaction_id' => '',
            'amount' => 0,
            'raw' => $payload,
        ];
    }
}