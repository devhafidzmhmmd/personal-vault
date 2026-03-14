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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'proman' => [
        'auth_url' => env('PROMAN_AUTH_URL', 'https://your-proman-auth-url.com'),
        'submit_url' => env('PROMAN_SUBMIT_URL', 'https://your-proman-submit-url.com'),
        'update_progress_url' => env('PROMAN_UPDATE_PROGRESS_URL', 'https://dcktrp.jakarta.go.id/proman/api/tasks/update-progress'),
        'app_key' => env('PROMAN_APP_KEY', 'your-proman-app-key'),
    ],
];
