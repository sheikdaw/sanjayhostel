<?php

return [
    /*
    |--------------------------------------------------------------------------
    | iClock WebAPI Configuration
    |--------------------------------------------------------------------------
    */

    'url' => env('EBIOSERVER_URL', 'http://182.76.161.219:81/iclock/WebAPIService.asmx'),
    'api_key' => env('EBIOSERVER_API_KEY', ''),
    'username' => env('EBIOSERVER_USERNAME', ''),
    'password' => env('EBIOSERVER_PASSWORD', ''),
    'location_code' => env('EBIOSERVER_LOCATION_CODE', 'HOSTEL_MAIN'),

    /*
    |--------------------------------------------------------------------------
    | Verification Types
    |--------------------------------------------------------------------------
    */
    'verification_types' => [
        'face' => '16',
        'face_fingerprint' => '17',
        'face_password' => '18',
        'face_card' => '19',
        'face_fingerprint_card' => '20',
        'face_fingerprint_password' => '21',
        'fingerprint' => '2',
        'fingerprint_password' => '6',
        'card' => '4',
        'password' => '3',
    ],
];
