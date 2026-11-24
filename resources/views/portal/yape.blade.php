@extends('portal.layout')
@section('titulo', 'Yape · Prueba')
@section('contenido')
<div class="citizen-banner"><span class="citizen-eyebrow">PAGO DE PRUEBA</span><h1>Simular pago con Yape</h1><p>Comprueba el flujo sin usar dinero ni datos reales de Yape.</p></div>
<section class="card mx-auto" style="max-width:620px"><div class="card-body p-4">
<h2 class="h4">Total: S/ {{ number_format(config('portal.tarifa_centimos') / 100, 2) }}</h2>
@if($tramite->pago?->estado === 'PAGADO')
<div class="alert alert-success">El pago ya está confirmado.</div>
@elseif($tramite->pago?->mercadopago_preferencia_id || ($tramite->pago && $tramite->pago->proveedor !== 'MERCADOPAGO'))
<div class="alert alert-warning">Esta solicitud ya tiene un pago abierto con otro flujo. Para probar Yape Orders, crea una nueva solicitud de prueba. Se conserva el historial anterior.</div>
@else
@unless($disponible)<div class="alert alert-warning">Falta la configuración de Yape Orders. Guarda la Public Key de prueba en MERCADOPAGO_PUBLIC_KEY.</div>@endunless
<form method="POST" action="{{ route('yape.pagar') }}">@csrf
<label for="celular" class="form-label mt-3">Resultado que deseas probar</label>
<select id="celular" name="celular" class="form-select mb-3">
<option value="111111111">Pago aprobado · 111111111</option>
<option value="111111113">Saldo insuficiente · 111111113</option>
<option value="111111114">Pago rechazado · 111111114</option>
</select>
<dl class="row small text-muted"><dt class="col-5">Correo ficticio</dt><dd class="col-7">test_payer@example.com</dd><dt class="col-5">Código de prueba</dt><dd class="col-7">123456</dd></dl>
<button class="btn btn-primary w-100" @disabled(!$disponible)>{{ $tramite->pago?->yape_intento ? 'Comprobar resultado' : 'Simular pago con Yape' }}</button>
<p class="small text-muted mt-3 mb-0">La confirmación se consulta en Mercado Pago. No necesitas abrir tu aplicación Yape ni escanear un QR.</p>
</form>
@endif
<a href="{{ route('portal.estado') }}" class="d-inline-block mt-4">Volver a mi solicitud</a>
</div></section>
@endsection
