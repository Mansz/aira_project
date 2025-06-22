<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter([
        // Local Development
        env('APP_ENV') === 'local' ? 'http://localhost:3000' : null,  // React Admin Panel (default)
        env('APP_ENV') === 'local' ? 'http://localhost:5176' : null,  // Vite port (admin panel)
        env('APP_ENV') === 'local' ? 'http://localhost:5178' : null,  // Vite port (admin panel)
        env('APP_ENV') === 'local' ? 'http://localhost:5179' : null,  // Vite port (admin panel)
        env('APP_ENV') === 'local' ? 'http://localhost:5180' : null,  // Current Vite port (admin panel)
        env('APP_ENV') === 'local' ? 'http://localhost:8000' : null,  // Laravel API
        env('APP_ENV') === 'local' ? 'http://127.0.0.1:3000' : null,  // React Admin Panel (127.0.0.1)
        env('APP_ENV') === 'local' ? 'http://127.0.0.1:5176' : null,  // Vite (admin panel)
        env('APP_ENV') === 'local' ? 'http://127.0.0.1:5178' : null,  // Vite (admin panel)
        env('APP_ENV') === 'local' ? 'http://127.0.0.1:5179' : null,  // Vite (admin panel)
        env('APP_ENV') === 'local' ? 'http://127.0.0.1:5180' : null,  // Current Vite (admin panel)
        
        // Production URLs
        env('FRONTEND_URL'),      // React frontend
        env('ADMIN_PANEL_URL'),   // Admin panel
        env('ANDROID_APP_URL'),   // Android app domain
    ]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        'X-HTTP-Method-Override',
    ],

    'exposed_headers' => [
        'Authorization',
    ],

    'max_age' => 60 * 60, // 1 hour

    'supports_credentials' => true,

    'paths_patterns' => [
        '^/api/' => true,
        '^/sanctum/' => true,
    ],
];
