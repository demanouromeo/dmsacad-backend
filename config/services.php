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

    // Resolved once here (bootstrap-time config loading) rather than via a raw env('JWT_SECRET')
    // call at each of the 5 places that verify/sign a token (AccountController::login/refresh/
    // logout, JwtMiddleware, RoleMiddleware) - on this XAMPP/Windows setup (Apache mpm_winnt, a
    // threaded MPM) calling env() directly mid-request intermittently returned something that
    // wasn't a string, throwing "Key material must be a string..." out of firebase/php-jwt's Key
    // constructor and 500ing whichever request hit it - reproduced directly by hammering
    // /api/accounts/refresh in a loop (~1 in 20 requests failed). getenv()/putenv() are documented
    // as not thread-safe under concurrent requests on a threaded SAPI; routing every read through
    // config('services.jwt_secret') instead keeps the env() call to Laravel's own single
    // bootstrap-time config resolution rather than repeating it ad hoc later in the request.
    'jwt_secret' => env('JWT_SECRET'),

];
