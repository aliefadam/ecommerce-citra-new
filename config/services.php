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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'midtrans' => [
        'mode' => env('MIDTRANS_MODE', filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN) ? 'production' : 'sandbox'),
        'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
        'server_key' => env('MIDTRANS_SERVER_KEY', ''),
        'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),
        'connect_timeout' => (int) env('MIDTRANS_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('MIDTRANS_TIMEOUT', 30),
        'retry_times' => (int) env('MIDTRANS_SAFE_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('MIDTRANS_SAFE_RETRY_SLEEP', 250),
    ],

    'wa_gateway' => [
        'mode' => env('WA_GATEWAY_MODE', 'sandbox'),
        'url' => env('WA_GATEWAY_URL', 'https://wa-gateway.dokterkoding.my.id'),
        'token' => env('WA_GATEWAY_TOKEN', ''),
        'timeout' => (int) env('WA_GATEWAY_TIMEOUT', 10),
        'retry_times' => (int) env('WA_GATEWAY_RETRY_TIMES', 1),
        'retry_sleep' => (int) env('WA_GATEWAY_RETRY_SLEEP', 200),
    ],

    'rajaongkir' => [
        'mode' => env('RAJAONGKIR_MODE', 'sandbox'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'api_key' => env('RAJAONGKIR_API_KEY', env('API_KEY_RAJAONGKIR', '')),
        'couriers' => env('RAJAONGKIR_COURIERS', 'jne:sicepat:jnt'),
        'tracking_cache_minutes' => (int) env('RAJAONGKIR_TRACKING_CACHE_MINUTES', 15),
        'connect_timeout' => (int) env('RAJAONGKIR_CONNECT_TIMEOUT', 5),
        'timeout' => (int) env('RAJAONGKIR_TIMEOUT', 20),
        'retry_times' => (int) env('RAJAONGKIR_SAFE_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('RAJAONGKIR_SAFE_RETRY_SLEEP', 250),
    ],

    'integration_certification' => [
        'email_allowlist' => array_values(array_filter(array_map('trim', explode(',', (string) env('INTEGRATION_SMOKE_EMAIL_ALLOWLIST', ''))))),
        'whatsapp_allowlist' => array_values(array_filter(array_map('trim', explode(',', (string) env('INTEGRATION_SMOKE_WHATSAPP_ALLOWLIST', ''))))),
        'allow_production' => filter_var(env('INTEGRATION_SMOKE_ALLOW_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
