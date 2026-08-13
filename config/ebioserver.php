<?php

return [
    /*
    |--------------------------------------------------------------------------
    | eBioServer Configuration
    |--------------------------------------------------------------------------
    */
    
    'url' => env('EBIOSERVER_URL', 'http://ebioservernew.esslsecurity.com:99/webservice.asmx'),
    'username' => env('EBIOSERVER_USERNAME', 'essl'),
    'password' => env('EBIOSERVER_PASSWORD', 'essl'),
    'location_code' => env('EBIOSERVER_LOCATION_CODE', 'HOSTEL_MAIN'),
    
    /*
    |--------------------------------------------------------------------------
    | Device Verification Types
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