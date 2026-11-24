<?php

namespace App\Http\Controllers;

use App\Models\{PagoTramite, TramitePublico};
use App\Services\YapeOrdersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB};
use Illuminate\Support\Str;

class YapeOrdersController extends Controller
{
    private function solicitud(Request $request): TramitePublico {
        $a = $request->session()->get('portal_acceso');
        abort_unless(is_array($a) && ($a['expira'] ?? 0) > now()->timestamp, 403);
        $t = TramitePublico::where('codigo', $a['codigo'])->whereNotNull('email_verificado_at')->firstOrFail();
        abort_unless($t->identidad_estado === 'DATOS_CONTRASTADOS', 409);
        return $t;
    }

    public function formulario(Request $request) {
        $t = $this->solicitud($request);
        return response()->view('portal.yape', ['tramite' => $t, 'disponible' => app(YapeOrdersService::class)->disponible()])
            ->header('Cache-Control', 'private, no-store');
    }

    public function pagar(Request $request, YapeOrdersService $yape) {
        $t = $this->solicitud($request);
        // Allow only documented dummy values. Do not collect real OTPs or citizen data.
        $input = $request->validate(['celular' => ['required', 'regex:/^11111111[1-8]$/D']]);
        if (!$yape->disponible()) return back()->with('error', 'Falta configurar la Public Key de prueba de Mercado Pago.');
        try {
            $resultado = Cache::store('file')->lock('mp-checkout:'.$t->id, 90)->block(3, function () use ($t, $yape, $input) {
                $pago = DB::transaction(function () use ($t) {
                    $actual = TramitePublico::lockForUpdate()->findOrFail($t->id);
                    abort_unless($actual->estado === 'PENDIENTE_PAGO', 409);
                    $p = PagoTramite::firstOrCreate(['tramite_publico_id' => $t->id], [
                        'proveedor' => 'MERCADOPAGO', 'idempotencia' => (string) Str::uuid(),
                        'importe_centimos' => config('portal.tarifa_centimos'),
                    ]);
                    abort_unless($p->proveedor === 'MERCADOPAGO' && !$p->mercadopago_preferencia_id
                        && !$p->mercadopago_checkout_url && $p->estado !== 'PAGADO', 409);
                    return $p;
                });
                return $yape->ejecutar($pago, $input['celular']);
            });
            if ($resultado === 'aprobado') return redirect()->route('portal.estado')->with('pago_confirmado', true);
            return back()->with('exito', $resultado === 'rechazado'
                ? 'Yape devolvió el rechazo simulado. No se confirmó el pago. Usa otra solicitud de prueba para otro escenario.'
                : 'La orden sigue pendiente. Pulsa comprobar para consultar la misma orden, sin crear otro pago.');
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException|\Illuminate\Http\Client\ConnectionException|\RuntimeException $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) throw $e;
            return back()->with('error', 'No se pudo confirmar la simulación. Si se envió la orden, se conserva el intento para evitar duplicados.');
        }
    }
}
