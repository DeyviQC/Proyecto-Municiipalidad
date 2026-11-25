<?php

namespace App\Http\Controllers;

use App\Models\TramitePublico;
use App\Services\AuditoriaService;
use App\Services\FirmaDocumentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EntregaDocumentoController extends Controller
{
    private function disponible(TramitePublico $tramite): void
    {
        abort_unless($tramite->estado === 'APROBADA' && $tramite->pago?->estado === 'PAGADO', 403);
    }

    public function preparar(Request $request, TramitePublico $tramite, FirmaDocumentoService $firma, AuditoriaService $audit)
    {
        $input = $request->validate(['version' => 'required|integer', 'modo' => ['sometimes', Rule::in(['original', 'recuadro'])],
            'pagina' => 'required_unless:modo,original|integer|min:1|max:100',
            'posicion' => ['required_unless:modo,original', Rule::in(['inferior_derecha', 'inferior_izquierda', 'superior_derecha', 'superior_izquierda'])]]);
        DB::transaction(function () use ($request, $tramite, $input, $firma, $audit) {
            $actual = TramitePublico::lockForUpdate()->findOrFail($tramite->id);
            $this->disponible($actual);
            if ($actual->version !== (int) $input['version']) throw ValidationException::withMessages(['firma' => 'La solicitud cambió. Recarga la página.']);
            if (isset($actual->datos['entrega']['firmado_ruta'])) throw ValidationException::withMessages(['firma' => 'Ya hay un PDF firmado cargado. No se puede reemplazar.']);
            $entrega = ($input['modo'] ?? '') === 'original'
                ? $firma->prepararOriginal($actual)
                : $firma->preparar($actual, $input['pagina'], $input['posicion']);
            $entrega['preparado_por'] = $request->user()->id;
            $actual->update(['datos' => array_merge($actual->datos, ['entrega' => $entrega]), 'version' => $actual->version + 1]);
            $audit->registrar('PREPARAR_FIRMA', 'Portal ciudadano', $actual->id,
                ($input['modo'] ?? '') === 'original' ? 'Copia original para firma manual, sin recuadro añadido.' : 'Preparación de PDF con logo municipal.');
        });
        if ($request->expectsJson()) {
            return response()->json(['version' => $tramite->fresh()->version,
                'descarga' => route('entrega.archivo', [$tramite, 'preparado']).'?descargar=1'])
                ->header('Cache-Control', 'private, no-store');
        }
        return back()->with('exito', 'Documento listo para descargar y firmar.');
    }

    public function archivo(Request $request, TramitePublico $tramite, string $tipo)
    {
        abort_unless(in_array($tipo, ['preparado', 'firmado', 'original'], true), 404);
        abort_unless(in_array($tramite->estado, ['APROBADA', 'LISTA'], true), 403);
        $ruta = $tipo === 'original'
            ? $tramite->acta?->pdf_ruta
            : ($tramite->datos['entrega'][$tipo.'_ruta'] ?? null);
        abort_unless($ruta && Storage::disk('local')->exists($ruta), 404);
        app(AuditoriaService::class)->registrar('VER_DOCUMENTO_ENTREGA', 'Portal ciudadano', $tramite->id, 'Documento '.$tipo);
        $response = $request->boolean('descargar')
            ? Storage::disk('local')->download($ruta, 'solicitud-'.$tramite->id.'-'.$tipo.'.pdf')
            : response()->file(Storage::disk('local')->path($ruta), ['Content-Type' => 'application/pdf']);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }

    public function cargar(Request $request, TramitePublico $tramite, FirmaDocumentoService $firma, AuditoriaService $audit)
    {
        $request->validate(['version' => 'required|integer', 'archivo' => 'required|file|mimes:pdf|max:20480']);
        $rutaGuardada = null;
        try {
            DB::transaction(function () use ($request, $tramite, $firma, $audit, &$rutaGuardada) {
                $actual = TramitePublico::lockForUpdate()->findOrFail($tramite->id);
                $this->disponible($actual);
                $entrega = $actual->datos['entrega'] ?? [];
                if ($actual->version !== $request->integer('version') || isset($entrega['firmado_ruta'])) {
                    throw ValidationException::withMessages(['archivo' => 'La solicitud cambió o ya tiene un PDF firmado. Recarga para continuar.']);
                }
                $disk = Storage::disk('local');
                $pdf = $request->file('archivo')->get();
                // Older stamped drafts remain verifiable; a signature over the original
                // can also be uploaded directly, without any prior download or preparation.
                $preparado = isset($entrega['preparado_ruta']) && $disk->exists($entrega['preparado_ruta'])
                    ? $disk->get($entrega['preparado_ruta']) : null;
                if ($preparado !== null && str_starts_with($pdf, $preparado)) {
                    if (!isset($entrega['preparado_sha256']) || ! hash_equals($entrega['preparado_sha256'], hash('sha256', $preparado))) abort(409);
                } else {
                    $entrega = array_merge($entrega, $firma->prepararOriginal($actual));
                    $preparado = $disk->get($entrega['preparado_ruta']);
                }
                $resultado = $firma->verificar($pdf, $preparado);
                $rutaGuardada = 'private/entregas/'.$actual->id.'/'.Str::uuid().'-firmado.pdf';
                if (! $disk->put($rutaGuardada, $pdf)) throw new \RuntimeException('No se pudo guardar el documento.');
                $entrega = array_merge($entrega, $resultado, ['firmado_ruta' => $rutaGuardada,
                    'firmado_sha256' => hash('sha256', $pdf), 'cargado_por' => $request->user()->id, 'cargado_at' => now()->toIso8601String()]);
                $actual->update(['datos' => array_merge($actual->datos, ['entrega' => $entrega]), 'version' => $actual->version + 1]);
                $audit->registrar('CARGAR_FIRMA', 'Portal ciudadano', $actual->id, 'Integridad criptográfica verificada; pendiente de revisión institucional.');
            });
        } catch (\Throwable $e) {
            if ($rutaGuardada) Storage::disk('local')->delete($rutaGuardada);
            throw $e;
        }
        return back()->with('exito', 'Firma recibida e integridad verificada. Revisa el certificado y el contenido antes de publicar.');
    }

    public function publicar(Request $request, TramitePublico $tramite, AuditoriaService $audit)
    {
        $request->validate(['version' => 'required|integer', 'revision_firma' => 'accepted', 'sustento_firma' => 'required|string|min:15|max:1500']);
        DB::transaction(function () use ($request, $tramite, $audit) {
            $actual = TramitePublico::lockForUpdate()->findOrFail($tramite->id);
            $this->disponible($actual);
            $entrega = $actual->datos['entrega'] ?? [];
            if ($actual->version !== $request->integer('version') || ($entrega['integridad'] ?? null) !== 'VERIFICADA' || empty($entrega['firmado_ruta'])) {
                throw ValidationException::withMessages(['firma' => 'Primero carga un PDF con firma válida y recarga la solicitud.']);
            }
            $disk = Storage::disk('local');
            abort_unless($disk->exists($entrega['firmado_ruta']) && hash_equals($entrega['firmado_sha256'], hash_file('sha256', $disk->path($entrega['firmado_ruta']))), 409);
            $entrega['revision'] = ['usuario_id' => $request->user()->id, 'fecha' => now()->toIso8601String(), 'sustento' => $request->string('sustento_firma')->toString()];
            $actual->update(['estado' => 'LISTA', 'datos' => array_merge($actual->datos, ['entrega' => $entrega]), 'version' => $actual->version + 1]);
            $actual->eventos()->create(['usuario_id' => $request->user()->id, 'estado' => 'LISTA', 'mensaje' => 'Tu documento firmado está disponible para descargar en este seguimiento.']);
            $audit->registrar('PUBLICAR_DOCUMENTO', 'Portal ciudadano', $actual->id, 'Documento disponible en seguimiento protegido.');
        });
        $mensaje = 'Documento publicado. El ciudadano ya puede descargarlo y recibió el aviso.';
        // If the citizen asked for e-mail delivery, send it now instead of needing another click.
        try {
            if (app(\App\Services\EnvioDocumentoCorreo::class)->enviar($tramite->id, 'PDF firmado enviado automáticamente al publicar, a petición del ciudadano.')) {
                $mensaje .= ' También se envió por correo.';
            }
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface|\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return back()->with('exito', $mensaje)->with('error', 'No se pudo enviar el correo automáticamente. Usa «Enviar PDF por correo» para reintentar.');
        }
        return back()->with('exito', $mensaje);
    }

    public function descartar(Request $request, TramitePublico $tramite, AuditoriaService $audit)
    {
        $request->validate(['version' => 'required|integer', 'motivo_descarte' => 'required|string|min:10|max:500']);
        DB::transaction(function () use ($request, $tramite, $audit) {
            $actual = TramitePublico::lockForUpdate()->findOrFail($tramite->id);
            $this->disponible($actual);
            if ($actual->version !== $request->integer('version')) throw ValidationException::withMessages(['firma' => 'La solicitud cambió. Recarga la página.']);
            $datos = $actual->datos;
            // Retain private files for audit; never overwrite or edit the uploaded signature.
            if (isset($datos['entrega'])) $datos['entregas_descartadas'][] = $datos['entrega'];
            unset($datos['entrega']);
            $actual->update(['datos' => $datos, 'version' => $actual->version + 1]);
            $audit->registrar('DESCARTAR_BORRADOR_FIRMA', 'Portal ciudadano', $actual->id, $request->string('motivo_descarte')->toString());
        });
        return back()->with('exito', 'Borrador retirado. Puedes preparar y firmar una nueva copia.');
    }
}
