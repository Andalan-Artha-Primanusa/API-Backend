<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://salmon-octopus-221724.hostingersite.com',
        'https://regal-brigadeiros-b9aa86.netlify.app',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];