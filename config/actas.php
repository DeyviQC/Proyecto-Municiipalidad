<?php

return [
    /*
     * Tamaño máximo permitido para los PDF escaneados, en kilobytes.
     */
    'pdf_max_kb' => (int) env('ACTAS_PDF_MAX_KB', 10240),
];
