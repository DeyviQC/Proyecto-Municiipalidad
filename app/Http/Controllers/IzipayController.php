<?php
namespace App\Http\Controllers;

use App\Models\PagoTramite;
use App\Models\TramitePublico;
use App\Services\IzipayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IzipayController extends Controller
{
    public function retorno(Request $request, IzipayService $izipay)
    {
        if ($motivo = $izipay->errorFirma($request->all())) {
            \Illuminate\Support\Facades\Log::warning('Izipay: retorno rechazado', ['motivo' => $motivo]);
            return response()->view('portal.izipay-error', ['diagnostico' => app()->environment('local') ? $motivo : null], 401)
                ->header('Cache-Control', 'private, no-store');
        }
        $codigoPagado = $this->confirmar($request, $izipay, false);
        if ($codigoPagado) {
            $destino = $request->session()->get('portal_acceso.codigo') === $codigoPagado ? 'portal.estado' : 'portal.seguimiento';
            return redirect()->route($destino)->with('pago_confirmado', true);
        }
        return redirect()->route('portal.seguimiento')->with('exito', 'Respuesta de Izipay recibida. Consulta tu solicitud con su referencia y correo.');
    }

    public function notificacion(Request $request, IzipayService $izipay)
    {
        $this->confirmar($request, $izipay, true);
        return response('OK', 200)->header('Cache-Control', 'no-store');
    }

    private function confirmar(Request $request, IzipayService $izipay, bool $ipn): ?string
    {
        abort_unless($izipay->verificarFirma($request->all(), $ipn), 401);
        $answer = json_decode($request->input('kr-answer'), true);
        abort_unless(is_array($answer) && ($answer['shopId'] ?? null) === config('izipay.username') && ($answer['orderCycle'] ?? null) === 'CLOSED', 422);
        abort_unless(($answer['orderDetails']['mode'] ?? null) === 'TEST', 422);
        return DB::transaction(function () use ($answer) {
            $pago = PagoTramite::where('idempotencia', $answer['orderDetails']['orderId'] ?? '')->where('proveedor', 'IZIPAY')->lockForUpdate()->firstOrFail();
            abort_unless(($answer['orderDetails']['orderTotalAmount'] ?? null) === $pago->importe_centimos && ($answer['orderDetails']['orderCurrency'] ?? null) === 'PEN', 422);
            if (($answer['orderStatus'] ?? '') !== 'PAID') return null;
            if ($pago->estado === 'PAGADO') return $pago->tramite->codigo;
            $tramite = $pago->tramite()->lockForUpdate()->firstOrFail();
            abort_unless($tramite->estado === 'PENDIENTE_PAGO', 409);
            $pago->update(['estado' => 'PAGADO', 'pagado_at' => now()]);
            $tramite->update(['estado' => 'PENDIENTE', 'version' => $tramite->version + 1]);
            $tramite->eventos()->create(['estado' => 'PENDIENTE', 'mensaje' => 'Pago de prueba confirmado por Izipay. Solicitud pendiente de revisión municipal. No representa un abono bancario real.']);
            return $tramite->codigo;
        });
    }
}
