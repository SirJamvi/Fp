<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        // --- Izin untuk aplikasi Ionic native ---
        'capacitor://localhost',
        'ionic://localhost', 
        'http://localhost',
        'http://localhost:8100',
        'http://localhost:8101',
        
        // --- Izin untuk domain produksi Anda ---
        'https://resdigaza.my.id',
        
        // --- Tambahan untuk mobile app ---
        'file://',
        'http://192.168.*',
        'http://10.*',  
    ],
    'allowed_origins_patterns' => [
        // Pattern untuk IP lokal dan mobile
        '/^http:\/\/192\.168\.\d+\.\d+/',
        '/^http:\/\/10\.\d+\.\d+\.\d+/',
        '/^capacitor:\/\/.*/',
        '/^ionic:\/\/.*/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];