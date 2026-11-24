<?php

return [
    'http_ca_file' => env('PORTAL_HTTP_CA_FILE'),
    'whatsapp_contacto' => env('PORTAL_WHATSAPP_CONTACTO'),
    'tarifa_centimos' => 1030,
    'terminos_version' => 'demo-2026-10-13-v1',
    'apisperu_token' => env('APISPERU_TOKEN'),
    'taypi' => [
        'sandbox' => env('TAYPI_SANDBOX', true),
        'public_key' => env('TAYPI_PUBLIC_KEY'),
        'secret_key' => env('TAYPI_SECRET_KEY'),
        'webhook_secret' => env('TAYPI_WEBHOOK_SECRET'),
    ],
];
