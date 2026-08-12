<?php

return [
    // UAT (sandbox) or PRODUCTION
    'env' => env('PHONEPE_ENV', 'UAT'),

    // From PhonePe Business Dashboard -> Developer Settings (V2 credentials)
    'client_id' => env('PHONEPE_CLIENT_ID'),
    'client_secret' => env('PHONEPE_CLIENT_SECRET'),
    'client_version' => env('PHONEPE_CLIENT_VERSION', 1),

    // Salt key for checksum generation
    'salt_key' => env('PHONEPE_SALT_KEY', '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399'),
    'salt_index' => env('PHONEPE_SALT_INDEX', 1),

    // API Base URL
    'base_url' => env('PHONEPE_ENV', 'UAT') === 'PROD' 
        ? 'https://api.phonepe.com/apis/hermes' 
        : 'https://api-preprod.phonepe.com/apis/pg-sandbox',

    // Where PhonePe redirects the user's browser back to after checkout
    'redirect_url' => env('PHONEPE_REDIRECT_URL'),

    // Webhook credentials
    'webhook_username' => env('PHONEPE_WEBHOOK_USERNAME'),
    'webhook_password' => env('PHONEPE_WEBHOOK_PASSWORD'),
];