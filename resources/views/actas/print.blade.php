<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Impresión - Acta {{ $acta->numero_acta }}/{{ $acta->anio }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; font-family: Georgia, serif; }
        .encabezado { text-align: center; border-bottom: 2px solid #0b3d66; padding-bottom: 15px; margin-bottom: 25px; }
        .fila { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dotted #ccc; }
        .etiqueta { color: #555; font-size: .85rem; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print text-end mb-3">
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
    </div>

    <div class="encabezado d-flex align-items-center justify-content-between">
       <img src="{{ asset('img/logo.jpg') }}" alt="Escudo Municipal" style="width:80px;height:80px;object-fit:contain;">
       <div class="text-center flex-grow-1">
           <h5 class="mb-0">MUNICIPALIDAD DISTRITAL</h5>
           <h6 class="mb-0">ANDRÉS AVELINO CÁCERES DORREGARAY</h6>
           <p class="mb-0 small">Sistema Web de Registro Civil</p>
           <h6 class="mt-3 mb-0">ACTA DE {{ $acta->tipo }}</h6>
           <p class="mb-0 small">N° {{ $acta->numero_acta }} — Año {{ $acta->anio }} — {{ $acta->codigo }}</p>
        </div>
        <img src="{{ asset('img/logo.jpg') }}" alt="Escudo Municipal" style="width:80px;height:80px;object-fit:contain;">
    </div>

    @if ($acta->tipo === 'NACIMIENTO' && $acta->nacimiento)
        @php($d = $acta->nacimiento)
        <div class="fila"><span class="etiqueta">Nombre</span><span>{{ $d->nombres }} {{ $d->apellido_paterno }} {{ $d->apellido_materno }}</span></div>
        <div class="fila"><span class="etiqueta">DNI</span><span>{{ $d->dni ?? '—' }}</span></div>
        <div class="fila"><span class="etiqueta">Fecha de nacimiento</span><span>{{ $d->fecha_nacimiento->format('d/m/Y') }} {{ $d->hora_nacimiento }}</span></div>
        <div class="fila"><span class="etiqueta">Lugar</span><span>{{ $d->lugar_nacimiento }}</span></div>
        <div class="fila"><span class="etiqueta">Padre</span><span>{{ $d->nombre_padre ?? '—' }}</span></div>
        <div class="fila"><span class="etiqueta">Madre</span><span>{{ $d->nombre_madre ?? '—' }}</span></div>
    @elseif ($acta->tipo === 'MATRIMONIO' && $acta->matrimonio)
        @php($d = $acta->matrimonio)
        <div class="fila"><span class="etiqueta">Contrayente 1</span><span>{{ $d->contrayente_1 }} — DNI {{ $d->c1_dni }}</span></div>
        <div class="fila"><span class="etiqueta">Contrayente 2</span><span>{{ $d->contrayente_2 }} — DNI {{ $d->c2_dni }}</span></div>
        <div class="fila"><span class="etiqueta">Fecha de matrimonio</span><span>{{ $d->fecha_matrimonio->format('d/m/Y') }}</span></div>
        <div class="fila"><span class="etiqueta">Lugar</span><span>{{ $d->lugar_matrimonio }}</span></div>
    @elseif ($acta->tipo === 'DEFUNCION' && $acta->defuncion)
        @php($d = $acta->defuncion)
        <div class="fila"><span class="etiqueta">Nombre</span><span>{{ $d->nombres }} {{ $d->apellido_paterno }} {{ $d->apellido_materno }}</span></div>
        <div class="fila"><span class="etiqueta">DNI</span><span>{{ $d->dni ?? '—' }}</span></div>
        <div class="fila"><span class="etiqueta">Fecha de defunción</span><span>{{ $d->fecha_defuncion->format('d/m/Y') }}</span></div>
        <div class="fila"><span class="etiqueta">Lugar</span><span>{{ $d->lugar_defuncion }}</span></div>
        <div class="fila"><span class="etiqueta">Causa</span><span>{{ $d->causa_defuncion }}</span></div>
    @endif

    <p class="mt-5 text-muted small">
        Documento generado por el Sistema Web de Registro Civil el {{ now()->format('d/m/Y H:i') }}.
        Registrado originalmente por {{ $acta->usuario->name }}.
    </p>
</body>
</html>
