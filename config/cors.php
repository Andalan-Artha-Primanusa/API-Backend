<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Semua endpoint API
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    // Semua HTTP method
    'allowed_methods' => ['*'],

    // Domain frontend yang diizinkan
    'allowed_origins' => [
        'http://localhost:5173', // React dev
        'https://moccasin-crab-693879.hostingersite.com', // React production
    ],

    'allowed_origins_patterns' => [],

    // Header yang boleh dikirim frontend
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    // Header response yang boleh dibaca frontend (optional)
    'exposed_headers' => [],

    // Cache preflight
    'max_age' => 0,

    // Pakai Bearer token, bukan cookie
    'supports_credentials' => false,

];
