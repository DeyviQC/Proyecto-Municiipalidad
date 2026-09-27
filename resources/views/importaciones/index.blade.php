@extends('layouts.app')

@section('titulo', 'Importación masiva')

@section('contenido')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-file-earmark-arrow-up me-1"></i> Importar actas de nacimiento (CSV)</h6>

                <p class="text-muted small">
                    Exporte su archivo desde Excel como <strong>CSV (delimitado por comas)</strong> con las
                    siguientes columnas en la primera fila:
                </p>
                <code class="d-block bg-light p-2 rounded small mb-3">{{ implode(',', $columnas) }}</code>

                <div class="alert alert-warning small">
                    La importación <strong>no sobrescribe</strong> registros existentes: toda fila cuyo
                    número/año de acta ya exista se reporta como error y se omite.
                </div>

                <form method="POST" action="{{ route('importaciones.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="archivo" accept=".csv,.txt" class="form-control mb-3" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Importar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        @if (session('resultadoImportacion'))
            @php($resultado = session('resultadoImportacion'))
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Resultado de la importación</h6>
                    <div class="alert alert-success">
                        {{ $resultado['importados'] }} actas importadas correctamente.
                    </div>
                    @if (count($resultado['errores']))
                        <div class="alert alert-danger">
                            <strong>{{ count($resultado['errores']) }} errores:</strong>
                            <ul class="mb-0 small">
                                @foreach ($resultado['errores'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
