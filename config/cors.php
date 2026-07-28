<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], //Allows all http methods (POST, GET, PUT, DELETE, etc.)

    //'allowed_origins' => ['*'],
    'allowed_origins' => [
        'https://dmseduc.netlify.app',
        'https://educ.dmsacad.com',
        'http://localhost:5173',
        'http://localhost:5174',
        'https://portfolio.dmsacad.com',
        'https://dmsacad.com',
        // Capacitor-wrapped Android/iOS app origins (no port, not a real web origin) - the
        // WebView serves the app from these fixed origins regardless of what backend target
        // ("remote"/"local") the user picks in-app, so both must be whitelisted for the
        // mobile app to ever reach either backend in CORS-enforced (i.e. non-Android-WebView-
        // exempt) request paths.
        'https://localhost',
        'capacitor://localhost',
        'http://localhost',
    ],
    //'allowed_origins' => ['http://localhost:5173', 'http://localhost:5174', 'http://localhost:5175'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    //'supports_credentials' => false,
    'supports_credentials' => true, //We set to true since we are using cookies/sessions

];
