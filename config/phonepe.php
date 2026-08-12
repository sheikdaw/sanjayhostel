<?php

return [
    'env' => env('PHONEPE_ENV', 'PROD'),

    'client_id' => env('PHONEPE_CLIENT_ID'),
    'client_secret' => env('PHONEPE_CLIENT_SECRET'),
    'client_version' => env('PHONEPE_CLIENT_VERSION', 1),

    'salt_key' => env('PHONEPE_SALT_KEY'),
    'salt_index' => env('PHONEPE_SALT_INDEX', 1),

    'base_url' => env('PHONEPE_ENV', 'PROD') === 'PROD' 
        ? 'https://api.phonepe.com/apis/hermes' 
        : 'https://api-preprod.phonepe.com/apis/pg-sandbox',

    'redirect_url' => env('PHONEPE_REDIRECT_URL'),

    'webhook_username' => env('PHONEPE_WEBHOOK_USERNAME'),
    'webhook_password' => env('PHONEPE_WEBHOOK_PASSWORD'),
];