<?php

return [
    'http_ca_file' => env('PORTAL_HTTP_CA_FILE'),
    'whatsapp_contacto' => env('PORTAL_WHATSAPP_CONTACTO'),
    'tarifa_centimos' => 1030,
    'terminos_version' => 'demo-2026-10-13-v1',
    // Días sin iniciar el pago antes de marcar la solicitud como VENCIDA.
    'dias_para_pagar' => (int) env('PORTAL_DIAS_PARA_PAGAR', 7),
    'apisperu_token' => env('APISPERU_TOKEN'),
];
