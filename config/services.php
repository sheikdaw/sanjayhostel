<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | eSSL Biometric Device
    |--------------------------------------------------------------------------
    */
    'essl' => [
        // Full SOAP endpoint — device is at 192.168.0.111, port 83
        'url'      => env('ESSL_URL', 'http://192.168.0.111:83/webservice.asmx'),

        // SOAP auth
        'api_key'  => env('ESSL_API_KEY', ''),
        'username' => env('ESSL_USERNAME', 'Admin'),
        'password' => env('ESSL_PASSWORD', 'Admin@123'),

        // Timeouts (seconds)
        'connect_timeout' => (int) env('ESSL_CONNECT_TIMEOUT', 5),
        'timeout'         => (int) env('ESSL_TIMEOUT', 30),

        // Toggle for local testing without a device
        'mock'     => (bool) env('ESSL_MOCK', false),

        // Default port (used if URL has no explicit port)
        'default_port' => (int) env('ESSL_DEFAULT_PORT', 83),
    ],

];
