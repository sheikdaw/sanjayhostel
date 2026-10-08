<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;

class PaymentGatewayManager
{
    /**
     * Get active gateway based on .env PAYMENT_GATEWAY
     *
     * Options:
     *   - upi_link (default)
     *   - axis
     */
    public static function driver(): PaymentGatewayInterface
    {
        $driver = strtolower(env('PAYMENT_GATEWAY', 'upi_link'));

        $gateway = match ($driver) {
            'axis'  => new AxisBankGateway(),
            default => new UpiLinkGateway(),
        };

        // If gateway not configured → fallback to UPI link
        if (!$gateway->isConfigured()) {
            return new UpiLinkGateway();
        }

        return $gateway;
    }
}