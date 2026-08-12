<?php

return [
    // UAT (sandbox) or PRODUCTION
    'env' => env('PHONEPE_ENV', 'UAT'),

    // From PhonePe Business Dashboard -> Developer Settings (V2 credentials)
    'client_id' => env('PHONEPE_CLIENT_ID'),
    'client_secret' => env('PHONEPE_CLIENT_SECRET'),
    'client_version' => env('PHONEPE_CLIENT_VERSION', 1),

    // Where PhonePe redirects the user's browser back to after checkout
    'redirect_url' => env('PHONEPE_REDIRECT_URL'),

    // Username/password YOU choose and register with PhonePe
    // (Developer Settings -> Webhook -> Create Webhook). PhonePe signs
    // every webhook request with SHA256(username:password) in the
    // Authorization header so you can verify it's genuinely them.
    'webhook_username' => env('PHONEPE_WEBHOOK_USERNAME'),
    'webhook_password' => env('PHONEPE_WEBHOOK_PASSWORD'),
];