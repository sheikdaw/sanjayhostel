<?php

namespace App\Contracts;

use App\Models\Hostel;
use App\Models\Resident;

interface PaymentGatewayInterface
{
    /**
     * Gateway name (for logging / DB)
     */
    public function getName(): string;

    /**
     * Is this gateway properly configured?
     */
    public function isConfigured(): bool;

    /**
     * Create a payment order.
     *
     * @return array{
     *     success: bool,
     *     mode: string,              // 'upi_link' | 'redirect' | 'checkout' | 'qr'
     *     redirect_url?: string,     // Where to redirect user (Axis page)
     *     qr_code?: string,          // Base64 QR image or URL
     *     checkout_data?: array,     // JS SDK data (Razorpay)
     *     upi_link?: string,         // upi:// deep link
     *     order_id: string,          // Gateway order ID (for tracking)
     *     amount: float,
     *     message?: string
     * }
     */
    public function createOrder(
        Resident $resident,
        Hostel $hostel,
        float $amount,
        array $meta = []
    ): array;

    /**
     * Verify webhook signature (for security).
     */
    public function verifyWebhook(array $headers, string $rawBody): bool;

    /**
     * Parse webhook payload → normalized result.
     *
     * @return array{
     *     success: bool,
     *     status: string,            // 'success' | 'failed' | 'pending'
     *     order_id: string,
     *     transaction_id: string,
     *     amount: float,
     *     resident_id?: int,
     *     hostel_id?: int,
     *     raw: array
     * }
     */
    public function parseWebhook(array $payload): array;
}