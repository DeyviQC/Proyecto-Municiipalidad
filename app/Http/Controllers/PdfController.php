<?php

namespace App\Http\Controllers;

use App\Models\Acta;
use App\Services\AuditoriaService;
use App\Services\PdfStorageService;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function __construct(
        private readonly PdfStorageService $pdfStorageService,
        private readonly AuditoriaService $auditoriaService,
    ) {
    }

    /**
     * GET /actas/{acta}/pdf
     * Entrega el PDF de forma segura: verifica autenticación (middleware auth),
     * autorización (Policy), existencia del archivo y registra la consulta
     * en auditoría, tal como exige la Sección 14.
     */
    public function ver(Acta $acta): Response
    {
        $this->authorize('view', $acta);

        if (! $this->pdfStorageService->existe($acta->pdf_ruta)) {
            abort(404, 'El documento PDF no se encuentra disponible en el servidor.');
        }

        $this->auditoriaService->registrar(
            AuditoriaService::VER_PDF,
            'Registro Civil',
            $acta->id,
            "Visualización del PDF del acta {$acta->tipo} N° {$acta->numero_acta}/{$acta->anio}."
        );

        $contenido = $this->pdfStorageService->contenido($acta->pdf_ruta);

        return response($contenido, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $acta->pdf_original . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function vista(Acta $acta)
    {
        $this->authorize('view', $acta);

        return view('pdf.viewer', compact('acta'));
    }
}
