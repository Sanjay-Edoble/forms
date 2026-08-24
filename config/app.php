<?php
/**
 * Edoble Forms — Application Configuration
 */
return [
    'name'     => env('APP_NAME', 'Edoble Forms'),
    'url'      => env('APP_URL', 'https://forms.edoble.in'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => env('APP_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),

    'session' => [
        'lifetime' => (int) env('SESSION_LIFETIME', 120),
        'secure'   => (bool) env('SESSION_SECURE', true),
    ],

    'admin_emails' => array_map('trim', explode(',', env('ADMIN_EMAILS', ''))),
];
