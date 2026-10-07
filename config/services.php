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
        'url'             => env('ESSL_URL', ''),          // http://192.168.1.140/iclock/WebAPIService.asmx
        'api_key'         => env('ESSL_API_KEY', '11'),
        'username'        => env('ESSL_USERNAME', ''),
        'password'        => env('ESSL_PASSWORD', ''),
        'connect_timeout' => env('ESSL_CONNECT_TIMEOUT', 5),
        'timeout'         => env('ESSL_TIMEOUT', 30),
        'mock'            => env('ESSL_MOCK', false),

        // Optional DB defaults
        'company_sname'    => env('ESSL_COMPANY', 'Default'),
        'department_sname' => env('ESSL_DEPARTMENT', 'Default'),
        'sub_department'   => env('ESSL_SUB_DEPT', ''),
        'location'         => env('ESSL_LOCATION', ''),
        'designation'      => env('ESSL_DESIGNATION', ''),
        'division'         => env('ESSL_DIVISION', ''),
        'grade'            => env('ESSL_GRADE', ''),
        'employment_type'  => env('ESSL_EMP_TYPE', 'Permanent'),
        'gender'           => env('ESSL_GENDER', 'Male'),
    ],

];
