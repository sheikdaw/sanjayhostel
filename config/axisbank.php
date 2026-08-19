<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Axis Bank Payment Configuration
    |--------------------------------------------------------------------------
    */
    
    // API Credentials
    'merchant_id' => env('AXIS_MERCHANT_ID', ''),
    'merchant_key' => env('AXIS_MERCHANT_KEY', ''),
    'merchant_secret' => env('AXIS_MERCHANT_SECRET', ''),
    
    // API URLs
    'base_url' => env('AXIS_BASE_URL', 'https://api.axisbank.com/v1'),
    'sandbox_url' => env('AXIS_SANDBOX_URL', 'https://sandbox.axisbank.com/v1'),
    
    // Mode: sandbox or live
    'mode' => env('AXIS_MODE', 'sandbox'),
    
    // Redirect URLs
    'return_url' => env('AXIS_RETURN_URL', '/guest/payment/callback'),
    'cancel_url' => env('AXIS_CANCEL_URL', '/guest/payment/cancel'),
    
    // Currency
    'currency' => env('AXIS_CURRENCY', 'INR'),
    
    // Webhook Secret
    'webhook_secret' => env('AXIS_WEBHOOK_SECRET', ''),
];