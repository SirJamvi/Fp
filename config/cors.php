<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], // Spesifik methods
    'allowed_origins' => [
        'http://localhost:8100',   // Port default Ionic
        'http://localhost:8101',   // Port yang Anda gunakan
        'http://127.0.0.1:8100',
        'http://127.0.0.1:8101',
        'capacitor://localhost',   // Untuk mobile app
        'ionic://localhost',       // Untuk mobile app
        'http://localhost:3000',   // Jika menggunakan React/Next.js
        'http://localhost:4200',   // Jika menggunakan Angular
    ],
    'allowed_origins_patterns' => [
        '/^http:\/\/localhost:\d+$/', // Pattern untuk localhost dengan port apapun
        '/^http:\/\/127\.0\.0\.1:\d+$/', // Pattern untuk 127.0.0.1 dengan port apapun
    ],
    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Origin',
        'Cache-Control',
        'Pragma',
    ],
    'exposed_headers' => [
        'Authorization',
        'X-Total-Count',
    ],
    'max_age' => 86400, // Cache preflight untuk 24 jam
    'supports_credentials' => true,
];