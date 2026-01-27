<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configurație pentru Google Analytics Data API (GA4)
    |
    */

    // Calea către fișierul JSON cu credențialele Service Account
    'credentials_path' => storage_path('app/google-analytics/service-account-credentials.json'),

    // Google Analytics 4 Property ID (ex: "123456789")
    // Găsește-l în Google Analytics > Admin > Property Settings
    'property_id' => env('GA_PROPERTY_ID', '281678807'),
];
