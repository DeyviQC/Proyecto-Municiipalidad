<?php

return [
    'python' => env('FIRMA_PYTHON', 'python'),
    'peru_credenciales' => env('FIRMAPERU_CREDENTIALS_FILE') ?: storage_path('app/private/firma/fwAuthorization.json'),
    'peru_puerto' => (int) env('FIRMAPERU_PORT', 48596),
];
