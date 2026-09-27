@extends('layouts.app')

@section('titulo', 'Buscar actas')

@section('contenido')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('actas.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small">N° de acta</label>
                <input type="text" name="numero_acta" class="form-control form-control-sm" value="{{ $filtros['numero_acta'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Año</label>
                <input type="number" name="anio" class="form-control form-control-sm" value="{{ $filtros['anio'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="NACIMIENTO" {{ ($filtros['tipo'] ?? '') === 'NACIMIENTO' ? 'selected' : '' }}>Nacimiento</option>
                    <option value="MATRIMONIO" {{ ($filtros['tipo'] ?? '') === 'MATRIMONIO' ? 'selected' : '' }}>Matrimonio</option>
                    <option value="DEFUNCION" {{ ($filtros['tipo'] ?? '') === 'DEFUNCION' ? 'selected' : '' }}>Defunción</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">DNI</label>
                <input type="text" name="dni" class="form-control form-control-sm" value="{{ $filtros['dni'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Nombre</label>
                <input type="text" name="nombre" class="form-control form-control-sm" value="{{ $filtros['nombre'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Apellido</label>
                <input type="text" name="apellido" class="form-control form-control-sm" value="{{ $filtros['apellido'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>N° Acta</th>
                    <th>Tipo</th>
                    <th>Año</th>
                    <th>Persona</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($actas as $acta)
                    <tr>
                        <td>{{ $acta->numero_acta }}</td>
                        <td>
                            <span class="badge
                                {{ $acta->tipo === 'NACIMIENTO' ? 'text-bg-primary' : ($acta->tipo === 'MATRIMONIO' ? 'text-bg-success' : 'text-bg-secondary') }}">
                                {{ $acta->tipo }}
                            </span>
                        </td>
                        <td>{{ $acta->anio }}</td>
                        <td>
                            @if ($acta->tipo === 'NACIMIENTO' && $acta->nacimiento)
                                {{ $acta->nacimiento->nombre_completo }}
                            @elseif ($acta->tipo === 'MATRIMONIO' && $acta->matrimonio)
                                {{ $acta->matrimonio->contrayente_1 }} &amp; {{ $acta->matrimonio->contrayente_2 }}
                            @elseif ($acta->tipo === 'DEFUNCION' && $acta->defuncion)
                                {{ $acta->defuncion->nombre_completo }}
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $acta->fecha_acta->format('d/m/Y') }}</td>
                        <td><span class="badge text-bg-warning-subtle text-warning-emphasis">{{ $acta->estado }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('actas.show', $acta) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('actas.pdf.visor', $acta) }}" class="btn btn-sm btn-outline-secondary" title="PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <a href="{{ route('actas.print', $acta) }}" class="btn btn-sm btn-outline-dark" title="Imprimir" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron actas con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $actas->links() }}
    </div>
</div>
@endsection
