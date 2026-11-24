@extends('layouts.app')
@section('titulo', $interno ? 'Solicitudes ciudadanas' : 'Mis solicitudes')
@section('contenido')
<div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
    <div><h1 class="h4">{{ $interno ? 'Atención de solicitudes' : 'Solicitudes de actas' }}</h1><p class="text-muted mb-0">{{ $interno ? 'Revisa los requisitos y registra la respuesta para cada solicitante.' : 'Consulta el avance y responde a las observaciones de la municipalidad.' }}</p></div>
    @unless($interno)<a href="{{ route('ciudadano.solicitudes.create') }}" class="btn btn-primary align-self-start">Solicitar acta</a>@endunless
</div>
<form method="GET" class="d-flex gap-2 mb-3"><label for="estado" class="visually-hidden">Filtrar por estado</label><select id="estado" name="estado" class="form-select" style="max-width:240px"><option value="">Todos los estados</option>@foreach(App\Models\Solicitud::ESTADOS as $clave => $nombre)<option value="{{ $clave }}" @selected(request('estado') === $clave)>{{ $nombre }}</option>@endforeach</select><button class="btn btn-outline-primary">Filtrar</button></form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Solicitud</th><th>Tipo y titular</th>@if($interno)<th>Solicitante</th>@endif<th>Estado</th><th>Presentada</th><th></th></tr></thead><tbody>
@forelse($solicitudes as $solicitud)
<tr><td>#{{ $solicitud->id }}</td><td>{{ ucfirst(strtolower($solicitud->tipo)) }}<br>{{ $solicitud->titular }}</td>@if($interno)<td>{{ $solicitud->usuario->name }}</td>@endif<td><span class="badge {{ in_array($solicitud->estado, ['OBSERVADA', 'RECHAZADA']) ? 'bg-danger' : 'bg-success' }}">{{ App\Models\Solicitud::ESTADOS[$solicitud->estado] }}</span></td><td>{{ $solicitud->created_at->format('d/m/Y H:i') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route($interno ? 'solicitudes.show' : 'ciudadano.solicitudes.show', $solicitud) }}">Ver solicitud</a></td></tr>
@empty<tr><td colspan="6" class="p-4 text-center">No hay solicitudes para mostrar.</td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $solicitudes->links() }}</div>
@endsection
