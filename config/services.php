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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        // Public absolute URL for email logos (Gmail cannot load localhost).
        'logo_url' => env('BREVO_LOGO_URL', 'https://shamandorascout.com/img/shamandora.png'),
    ],

    'whatsapp' => [
        'bridge_url' => env('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3010/send'),
        'bridge_base_url' => env('WHATSAPP_BRIDGE_BASE_URL'),
        'bridge_token' => env('WHATSAPP_BRIDGE_TOKEN'),
        // ponytail: WA bridge is down — set true when it is back
        'send_qr' => (bool) env('WHATSAPP_SEND_QR', false),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('EVENT_PROGRAM_AI_MODEL', 'gemini-2.5-flash'),
    ],

];
