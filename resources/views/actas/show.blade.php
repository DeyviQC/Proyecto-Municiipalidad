@extends('layouts.app')

@section('titulo', 'ACTA DE ' . $acta->tipo)

@section('contenido')
<div class="certificado mx-auto" style="max-width: 900px;">
    <div class="certificado-encabezado">
        <div class="subtitulo">Municipalidad Distrital Andrés Avelino Cáceres Dorregaray</div>
        <h3 class="mt-2 mb-0">ACTA DE {{ $acta->tipo }}</h3>
        <div class="text-muted mt-1">N° {{ $acta->numero_acta }} &nbsp;·&nbsp; Año {{ $acta->anio }} &nbsp;·&nbsp; {{ $acta->codigo }}</div>
        <div class="mt-3">
            <span class="badge-bloqueada">
                <i class="bi bi-shield-lock-fill me-1"></i> ACTA BLOQUEADA / SOLO LECTURA
            </span>
        </div>
    </div>

    <div class="p-4 p-md-5">
        @if ($acta->tipo === 'NACIMIENTO' && $acta->nacimiento)
            @php($d = $acta->nacimiento)
            <div class="row g-4">
                <div class="col-md-4"><div class="dato-label">Nombre</div><div class="dato-valor">{{ $d->nombres }}</div></div>
                <div class="col-md-4"><div class="dato-label">Apellidos</div><div class="dato-valor">{{ $d->apellido_paterno }} {{ $d->apellido_materno }}</div></div>
                <div class="col-md-4"><div class="dato-label">DNI</div><div class="dato-valor">{{ $d->dni ?? '—' }}</div></div>
                <div class="col-md-4"><div class="dato-label">Fecha de nacimiento</div><div class="dato-valor">{{ $d->fecha_nacimiento->format('d/m/Y') }}</div></div>
                <div class="col-md-4"><div class="dato-label">Hora</div><div class="dato-valor">{{ $d->hora_nacimiento ?? '—' }}</div></div>
                <div class="col-md-4"><div class="dato-label">Sexo</div><div class="dato-valor">{{ $d->sexo === 'M' ? 'Masculino' : 'Femenino' }}</div></div>
                <div class="col-md-6"><div class="dato-label">Lugar de nacimiento</div><div class="dato-valor">{{ $d->lugar_nacimiento }}</div></div>
                <div class="col-md-6"></div>
                <div class="col-md-6"><div class="dato-label">Padre</div><div class="dato-valor">{{ $d->nombre_padre ?? '—' }} <span class="text-muted small">({{ $d->dni_padre ?? '—' }})</span></div></div>
                <div class="col-md-6"><div class="dato-label">Madre</div><div class="dato-valor">{{ $d->nombre_madre ?? '—' }} <span class="text-muted small">({{ $d->dni_madre ?? '—' }})</span></div></div>
            </div>
        @elseif ($acta->tipo === 'MATRIMONIO' && $acta->matrimonio)
            @php($d = $acta->matrimonio)
            <div class="row g-4">
                <div class="col-12"><div class="dato-label">Contrayente 1</div><div class="dato-valor">{{ $d->contrayente_1 }} <span class="text-muted small">DNI {{ $d->c1_dni ?? '—' }}</span></div></div>
                <div class="col-12"><div class="dato-label">Contrayente 2</div><div class="dato-valor">{{ $d->contrayente_2 }} <span class="text-muted small">DNI {{ $d->c2_dni ?? '—' }}</span></div></div>
                <div class="col-md-6"><div class="dato-label">Fecha de matrimonio</div><div class="dato-valor">{{ $d->fecha_matrimonio->format('d/m/Y') }}</div></div>
                <div class="col-md-6"><div class="dato-label">Lugar</div><div class="dato-valor">{{ $d->lugar_matrimonio }}</div></div>
            </div>
        @elseif ($acta->tipo === 'DEFUNCION' && $acta->defuncion)
            @php($d = $acta->defuncion)
            <div class="row g-4">
                <div class="col-md-4"><div class="dato-label">Nombre</div><div class="dato-valor">{{ $d->nombres }}</div></div>
                <div class="col-md-4"><div class="dato-label">Apellidos</div><div class="dato-valor">{{ $d->apellido_paterno }} {{ $d->apellido_materno }}</div></div>
                <div class="col-md-4"><div class="dato-label">DNI</div><div class="dato-valor">{{ $d->dni ?? '—' }}</div></div>
                <div class="col-md-4"><div class="dato-label">Fecha de defunción</div><div class="dato-valor">{{ $d->fecha_defuncion->format('d/m/Y') }}</div></div>
                <div class="col-md-4"><div class="dato-label">Lugar de defunción</div><div class="dato-valor">{{ $d->lugar_defuncion }}</div></div>
                <div class="col-md-4"><div class="dato-label">Médico</div><div class="dato-valor">{{ $d->medico_certifica ?? '—' }}</div></div>
                <div class="col-12"><div class="dato-label">Causa de defunción</div><div class="dato-valor">{{ $d->causa_defuncion }}</div></div>
            </div>
        @endif

        @if ($acta->observaciones)
            <div class="mt-4"><div class="dato-label">Observaciones</div><div class="dato-valor">{{ $acta->observaciones }}</div></div>
        @endif

        <hr class="my-4" style="border-color: var(--dorado-claro);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Registrado por <strong>{{ $acta->usuario->name }}</strong> el {{ $acta->created_at->format('d/m/Y H:i') }}
            </span>
            <div>
                <a href="{{ route('actas.pdf.visor', $acta) }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF
                </a>
                <a href="{{ route('actas.print', $acta) }}" target="_blank" class="btn btn-outline-dark">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </a>
                <a href="{{ route('actas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>
@endsection