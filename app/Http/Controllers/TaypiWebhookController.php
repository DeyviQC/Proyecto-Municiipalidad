<?php

namespace App\Http\Controllers;

use App\Models\PagoTramite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaypiWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('portal.taypi.webhook_secret');
        abort_unless($secret, 503);
        abort_unless(strlen($request->getContent()) <= 10000 && hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), $request->header('Taypi-Signature', '')), 401);
        $datos = $request->validate(['event' => ['required', 'in:payment.completed,payment.expired'], 'payment_id' => ['required', 'uuid'], 'amount' => ['required', 'regex:/^[0-9]+\.[0-9]{2}$/'], 'currency' => ['required', 'in:PEN'], 'reference' => ['required', 'uuid'], 'status' => ['required', 'in:completed,expired']]);
        DB::transaction(function () use ($datos) {
            $pago = PagoTramite::where('proveedor_id', $datos['payment_id'])->lockForUpdate()->first();
            abort_unless($pago, 404);
            $tramite = $pago->tramite()->lockForUpdate()->firstOrFail();
            abort_unless($tramite->codigo === $datos['reference'] && $datos['amount'] === number_format($pago->importe_centimos / 100, 2, '.', ''), 422);
            if ($pago->estado === 'PAGADO') return;
            if ($datos['event'] === 'payment.expired' && $datos['status'] === 'expired') { $pago->update(['estado' => 'VENCIDO']); return; }
            abort_unless($datos['event'] === 'payment.completed' && $datos['status'] === 'completed', 422);
            $pago->update(['estado' => 'PAGADO', 'pagado_at' => now()]);
            $tramite->update(['estado' => 'PENDIENTE', 'version' => $tramite->version + 1]);
            $tramite->eventos()->create(['estado' => 'PENDIENTE', 'mensaje' => 'Pago confirmado. Solicitud pendiente de atención por el Registro Civil.']);
        });
        return response()->json(['ok' => true]);
    }
}
