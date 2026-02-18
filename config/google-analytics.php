<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics (GA4) Configuration
    |--------------------------------------------------------------------------
    | Toate valorile se citesc din .env. Pe server: setați GA_PROPERTY_ID și
    | puneți service-account-credentials.json în storage/app/google-analytics/
    */

    'credentials_path' => env('GA_CREDENTIALS_PATH')
        ?: storage_path('app/google-analytics/service-account-credentials.json'),

    'property_id' => env('GA_PROPERTY_ID', ''),

    'ssl_verify' => filter_var(env('GA_SSL_VERIFY', true), FILTER_VALIDATE_BOOLEAN),
];
