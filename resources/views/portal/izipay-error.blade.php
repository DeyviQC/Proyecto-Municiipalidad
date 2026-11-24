@extends('portal.layout')
@section('contenido')
<h1 class="h3">No pudimos verificar la confirmación de Izipay</h1>
<div class="alert alert-warning">La respuesta recibida no pudo validarse. Esta respuesta no ha marcado tu solicitud como pagada.</div>
<p>No realices otro pago para intentar corregir este mensaje. Consulta primero el estado de tu solicitud.</p>
@if($diagnostico)<p>Diagnóstico de desarrollo: <strong>{{ $diagnostico }}</strong>.</p>@endif
<a href="{{ route('portal.seguimiento') }}" class="btn btn-primary">Consultar mi solicitud</a>
@endsection
