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
    | eSSL Biometric Device  (eTimetracklite Web API Service)
    |--------------------------------------------------------------------------
    */
    'essl' => [
        'url'      => env('ESSL_URL', 'http://192.168.0.111:83/iclock/WebAPIService.asmx'),

        'api_key'  => env('ESSL_API_KEY', '11'),
        'username' => env('ESSL_USERNAME', 'Admin'),
        'password' => env('ESSL_PASSWORD', ''),

        'connect_timeout' => (int) env('ESSL_CONNECT_TIMEOUT', 5),
        'timeout'         => (int) env('ESSL_TIMEOUT', 30),

        'mock'         => filter_var(env('ESSL_MOCK', false), FILTER_VALIDATE_BOOLEAN),
        'default_port' => (int) env('ESSL_DEFAULT_PORT', 83),

        // ── AddMultipleEmployeesToDB masters ──
        // ⚠️ MUST match eTimetracklite desktop Short Names exactly (case-sensitive)
        'company_sname'    => env('ESSL_COMPANY_SNAME', 'Default'),
        'department_sname' => env('ESSL_DEPARTMENT_SNAME', 'Default'),
        'sub_department'   => env('ESSL_SUB_DEPARTMENT', ''),
        'location'         => env('ESSL_LOCATION', ''),
        'designation'      => env('ESSL_DESIGNATION', ''),
        'division'         => env('ESSL_DIVISION', ''),
        'grade'            => env('ESSL_GRADE', ''),
        'employment_type'  => env('ESSL_EMPLOYMENT_TYPE', 'Permanent'),
        'gender'           => env('ESSL_DEFAULT_GENDER', 'Male'),
    ],

];
