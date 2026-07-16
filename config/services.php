<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'onec' => [
        'base_url' => env('ONEC_BASE_URL', 'http://212.56.193.250/VOLTA_SQL/hs/CallCenterKPI'),
        'username' => env('ONEC_USERNAME', 'HTTPService'),
        'password' => env('ONEC_PASSWORD', ''),
    ],

    'mobile_analytics' => [
        'key' => env('MOBILE_ANALYTICS_KEY', ''),
    ],

    'mobile_crashes' => [
        'key' => env('MOBILE_CRASHES_KEY', ''),
    ],

];
