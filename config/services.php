<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Single resolution point for the JWT secret - every sign/verify site (AccountController,
    // JwtMiddleware, RoleMiddleware) reads config('services.jwt_secret') rather than env() directly.
    // Note this alone did not stop the intermittent "Key material must be a string" 401s under
    // concurrent requests on XAMPP's threaded Apache: config isn't cached, so this env() call still
    // ran per request through the thread-unsafe putenv/getenv adapter. The actual fix is
    // Env::disablePutenv() in bootstrap/app.php.
    'jwt_secret' => env('JWT_SECRET'),

];
