@extends('layouts.app')
@section('titulo', 'Solicitar acta')
@section('contenido')
<div class="card mx-auto" style="max-width:800px"><div class="card-body p-4"><h1 class="h4">Nueva solicitud</h1><p>Completa los datos que conoces. Presentar una solicitud no autoriza automáticamente la entrega de un acta.</p><form method="POST" action="{{ route('ciudadano.solicitudes.store') }}">@csrf @include('solicitudes.campos')<button class="btn btn-primary">Presentar solicitud</button><a class="btn btn-outline-secondary" href="{{ route('ciudadano.solicitudes.index') }}">Volver</a></form></div></div>
@endsection
