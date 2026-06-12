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
        'key' => env('RESEND_KEY'),
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

    'asaas' => [
        'api_key'       => env('ASAAS_API_KEY', ''),
        'environment'   => env('ASAAS_ENVIRONMENT', 'sandbox'),
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN', ''),
    ],

    'evolution' => [
        'base_url'       => env('EVOLUTION_API_BASE_URL', ''),
        'token'          => env('EVOLUTION_API_TOKEN', ''),
        // Instância já conectada do admin para envio de OTPs e notificações do sistema
        'admin_instance' => env('EVOLUTION_ADMIN_INSTANCE', ''),
    ],

    'cerebras' => [
        'api_key' => env('CEREBRAS_API_KEY', ''),
        'model'   => env('CEREBRAS_MODEL', 'gpt-oss-120b'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

];
