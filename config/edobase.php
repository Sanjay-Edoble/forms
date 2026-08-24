<?php
/**
 * Edoble Forms — Edobase API Configuration
 */
return [
    'url'        => rtrim(env('EDOBASE_URL', 'https://db.edoble.in/api/v1'), '/'),
    'public_key' => env('EDOBASE_PUBLIC_KEY', ''),
    'secret_key' => env('EDOBASE_SECRET_KEY', ''),
    'timeout'    => 30,
    'retry'      => 2,
];
