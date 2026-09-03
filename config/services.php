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

    'contabilidad' => [
        'url' => env('CONTABILIDAD_API_URL', 'http://contabilidad-api.test/api/v1'),
        'web_url' => env('CONTABILIDAD_WEB_URL'),
        'token' => env('CONTABILIDAD_API_TOKEN'),
        'cliente_uuid' => env('CONTABILIDAD_CLIENTE_UUID'),
        'connect_timeout' => env('CONTABILIDAD_CONNECT_TIMEOUT', 3),
        'timeout' => env('CONTABILIDAD_TIMEOUT', 10),
    ],

    'sdi_sso' => [
        'key' => env('SDI_SSO_KEY', 'sdi-local-integracion-2026-cambiar-en-produccion'),
        'vet_web_url' => env('VET_SDI_WEB_URL', 'http://vet-sdi_v13.test:8080/Paciente/Inicio'),
    ],

    'sdi_hub' => [
        'enabled' => env('SDI_HUB_ENABLED', true),
        'url' => env('SDI_HUB_URL', 'http://servidor-local.test'),
        'app' => env('SDI_HUB_APP', 'alimentos-laravel13'),
        'key' => env('SDI_HUB_KEY', 'sdi-local-alimentos-2026'),
        'timeout' => env('SDI_HUB_TIMEOUT', 5),
    ],

    'pedidos_recordatorio' => [
        'dias_anticipacion' => env('PEDIDO_RECORDATORIO_DIAS', 7),
        'envio_real' => env('PEDIDO_RECORDATORIO_ENVIO_REAL', false),
    ],
];
