<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    // Batasi hanya method HTTP yang benar-benar digunakan aplikasi.
    // Jangan gunakan ['*'] di production karena mengizinkan TRACE, CONNECT, dll.
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // WAJIB dikonfigurasi via .env di production:
    //   CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
    // Default hanya untuk development (localhost Vite dev server).
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 jam preflight cache — mengurangi jumlah OPTIONS request

    'supports_credentials' => false,
];
