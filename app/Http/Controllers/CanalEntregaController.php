<?php

namespace App\Http\Controllers;

use App\Models\TramitePublico;
use App\Mail\DocumentoFirmadoMail;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Mail, Storage};

class CanalEntregaController extends Controller
{
    public function guardar(Request $request)
    {
        $acceso = $request->session()->get('portal_acceso');
        abort_unless($acceso && ($acceso['expira'] ?? 0) > now()->timestamp, 403);
        $input = $request->validate(['correo' => 'sometimes|boolean', 'whatsapp' => 'sometimes|boolean',
            'numero' => ['nullable', 'regex:/^[1-9][0-9]{7,14}$/D']]);
        DB::transaction(function () use ($acceso, $input) {
            $t = TramitePublico::where('codigo', $acceso['codigo'])->lockForUpdate()->firstOrFail();
            abort_unless($t->email_verificado_at && $t->pago?->estado === 'PAGADO', 403);
            $previas = $t->datos['canales_entrega'] ?? [];
            // The e-mail form does not carry the WhatsApp choice: keep it unless sent explicitly.
            $whatsapp = array_key_exists('whatsapp', $input) ? (bool) $input['whatsapp'] : (bool) ($previas['whatsapp'] ?? false);
            $preferencias = array_merge($previas, ['correo' => (bool) ($input['correo'] ?? false), 'whatsapp' => $whatsapp,
                'numero' => $whatsapp ? ($input['numero'] ?? $previas['numero'] ?? null) : null, 'fecha' => now()->toIso8601String()]);
            $t->update(['datos' => array_merge($t->datos, ['canales_entrega' => $preferencias]), 'version' => $t->version + 1]);
        });
        return redirect()->route('portal.estado')->with('exito', 'Preferencias de entrega guardadas.');
    }

    /**
     * The citizen chooses WhatsApp and starts the chat themselves: we record the
     * choice and open WhatsApp with a ready message to the municipal number.
     * The institution never writes first; it only replies in that conversation.
     */
    public function whatsapp(Request $request, \App\Services\WhatsAppConsulta $consulta)
    {
        $acceso = $request->session()->get('portal_acceso');
        abort_unless($acceso && ($acceso['expira'] ?? 0) > now()->timestamp, 403);
        $enlace = DB::transaction(function () use ($acceso, $consulta) {
            $t = TramitePublico::where('codigo', $acceso['codigo'])->lockForUpdate()->firstOrFail();
            abort_unless($t->email_verificado_at && $t->pago?->estado === 'PAGADO', 403);
            $enlace = $consulta->enlace($t);
            abort_unless($enlace, 404);
            $previas = $t->datos['canales_entrega'] ?? [];
            $preferencias = array_merge($previas, ['correo' => (bool) ($previas['correo'] ?? false), 'whatsapp' => true,
                'whatsapp_iniciado_at' => $previas['whatsapp_iniciado_at'] ?? now()->toIso8601String(), 'fecha' => now()->toIso8601String()]);
            $t->update(['datos' => array_merge($t->datos, ['canales_entrega' => $preferencias]), 'version' => $t->version + 1]);
            return $enlace;
        });
        return redirect()->away($enlace)->header('Referrer-Policy', 'no-referrer');
    }

    public function correo(Request $request, TramitePublico $tramite, AuditoriaService $audit)
    {
        try {
            // The cache lock serialises concurrent clicks, so the SMTP call happens
            // outside the database transaction without holding a row lock open.
            Cache::lock('entrega-correo:'.$tramite->id, 120)->block(5, function () use ($tramite, $audit) {
                $t = TramitePublico::findOrFail($tramite->id);
                $datos = $t->datos;
                $entrega = $datos['entrega'] ?? [];
                abort_unless($t->estado === 'LISTA' && $t->pago?->estado === 'PAGADO' && $t->email_verificado_at
                    && !empty($datos['canales_entrega']['correo']) && !empty($entrega['revision']), 403);
                if (!empty($entrega['correo_enviado_at'])) return;
                $disk = Storage::disk('local');
                $ruta = $entrega['firmado_ruta'] ?? '';
                abort_unless($ruta && $disk->exists($ruta) && hash_equals($entrega['firmado_sha256'] ?? '', hash_file('sha256', $disk->path($ruta))), 409);
                Mail::to($t->email)->send(new DocumentoFirmadoMail($t->id, $ruta));
                DB::transaction(function () use ($t, $audit) {
                    $actual = TramitePublico::lockForUpdate()->findOrFail($t->id);
                    $datos = $actual->datos;
                    $datos['entrega']['correo_enviado_at'] = now()->toIso8601String();
                    $actual->update(['datos' => $datos, 'version' => $actual->version + 1]);
                    $audit->registrar('ENVIAR_DOCUMENTO_CORREO', 'Portal ciudadano', $actual->id, 'PDF firmado enviado al correo verificado a petición del ciudadano.');
                });
            });
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            return back()->with('error', 'No se pudo confirmar el envío del correo. Revisa la bandeja de salida antes de volver a intentarlo.');
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return back()->with('error', 'El envío ya está en curso. Espera unos segundos y recarga la página.');
        }
        return back()->with('exito', 'Documento enviado al correo verificado.');
    }
}
