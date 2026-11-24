@extends('layouts.app')
@section('titulo', 'Solicitud #'.$solicitud->id)
@section('contenido')
<a href="{{ route(auth()->user()->isCiudadano() ? 'ciudadano.solicitudes.index' : 'solicitudes.index') }}" class="btn btn-outline-secondary mb-3">Volver a solicitudes</a>
<div class="row g-4"><div class="col-lg-7"><div class="card"><div class="card-body">
<h1 class="h4">Solicitud #{{ $solicitud->id }}</h1><span class="badge {{ in_array($solicitud->estado, ['OBSERVADA', 'RECHAZADA']) ? 'bg-danger' : 'bg-success' }}">{{ App\Models\Solicitud::ESTADOS[$solicitud->estado] }}</span>
<dl class="mt-3"><dt>Tipo</dt><dd>{{ ucfirst(strtolower($solicitud->tipo)) }}</dd><dt>Titular</dt><dd>{{ $solicitud->titular }}</dd><dt>DNI informado</dt><dd>{{ $solicitud->dni_titular ?: 'No informado en la solicitud' }}</dd><dt>Fecha de inscripción informada</dt><dd>{{ $solicitud->fecha_acta?->format('d/m/Y') ?: 'No informada' }}</dd><dt>Referencia y motivo</dt><dd style="white-space:pre-wrap">{{ $solicitud->detalle }}</dd>@unless(auth()->user()->isCiudadano())<dt>Solicitante</dt><dd>{{ $solicitud->usuario->name }} · {{ $solicitud->usuario->email }}</dd>@endunless</dl>
@if($solicitud->estado === 'APROBADA')<p class="alert alert-success mb-0">Solicitud aprobada. La aprobación no equivale a la entrega del documento.</p>@endif
</div></div>
@can('update', $solicitud)<div class="card mt-3"><div class="card-body"><h2 class="h5">Subsanar observaciones</h2><p>Revisa la respuesta en el historial y corrige los datos necesarios.</p><form method="POST" action="{{ route('ciudadano.solicitudes.update', $solicitud) }}">@csrf @method('PATCH')<input type="hidden" name="version" value="{{ $solicitud->version }}">@include('solicitudes.campos')<button class="btn btn-primary">Enviar corrección</button></form></div></div>@endcan
@can('atender', $solicitud)
@if(count(App\Models\Solicitud::TRANSICIONES[$solicitud->estado] ?? []))
<div class="card mt-3"><div class="card-body"><h2 class="h5">Atender solicitud</h2><form method="POST" action="{{ route('solicitudes.atender', $solicitud) }}">@csrf @method('PATCH')<input type="hidden" name="version" value="{{ $solicitud->version }}"><label for="estado" class="form-label">Nuevo estado</label><select id="estado" name="estado" class="form-select mb-3" required>@foreach(App\Models\Solicitud::TRANSICIONES[$solicitud->estado] as $estado)<option value="{{ $estado }}">{{ App\Models\Solicitud::ESTADOS[$estado] }}</option>@endforeach</select><label for="mensaje" class="form-label">Respuesta visible para el solicitante</label><textarea id="mensaje" name="mensaje" rows="3" maxlength="2000" class="form-control mb-3" required>{{ old('mensaje') }}</textarea><button class="btn btn-primary">Guardar respuesta</button></form></div></div>
@endif
@endcan
</div><div class="col-lg-5"><div class="card"><div class="card-body"><h2 class="h5">Historial</h2>@foreach($solicitud->eventos as $evento)<div class="border-bottom py-3"><strong>{{ App\Models\Solicitud::ESTADOS[$evento->estado] }}</strong><div class="small text-muted">{{ $evento->created_at->format('d/m/Y H:i') }}</div><p class="mb-0" style="white-space:pre-wrap">{{ $evento->mensaje }}</p></div>@endforeach</div></div></div></div>
@endsection
