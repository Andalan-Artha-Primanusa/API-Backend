<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    // ✅ LOCAL ONLY
    'allowed_origins' => [
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
