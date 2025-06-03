<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'], // FIX: Ini yang penting - jangan kosong!
    'allowed_origins' => [
        'http://localhost:8100',   // Port default Ionic
        'http://localhost:8101',   // Port yang Anda gunakan
        'http://127.0.0.1:8100',
        'http://127.0.0.1:8101',
        'capacitor://localhost',   // Untuk mobile app
        'ionic://localhost',       // Untuk mobile app
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];