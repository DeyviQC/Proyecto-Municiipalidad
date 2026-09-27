@extends('layouts.app')

@section('titulo', 'Registrar acta')

@section('contenido')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('actas.store') }}" enctype="multipart/form-data" id="formActa">
            @csrf

            <h6 class="text-muted mb-3">PASO 1 — Tipo de acta</h6>
            <div class="row row-cols-3 g-3 mb-4">
                @foreach (['NACIMIENTO' => 'person-plus', 'MATRIMONIO' => 'heart', 'DEFUNCION' => 'flower1'] as $tipoOpcion => $icono)
                    <div class="col">
                        <input type="radio" class="btn-check" name="tipo" id="tipo{{ $tipoOpcion }}"
                               value="{{ $tipoOpcion }}" autocomplete="off"
                               {{ old('tipo') === $tipoOpcion ? 'checked' : '' }} required>
                        <label class="btn btn-outline-primary w-100 py-3" for="tipo{{ $tipoOpcion }}">
                            <i class="bi bi-{{ $icono }} d-block fs-3 mb-1"></i>
                            {{ ucfirst(strtolower($tipoOpcion)) }}
                        </label>
                    </div>
                @endforeach
            </div>

            <h6 class="text-muted mb-3">PASO 2 — Datos generales</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Número de acta</label>
                    <input type="text" name="numero_acta" class="form-control" value="{{ old('numero_acta') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Año</label>
                    <input type="number" name="anio" class="form-control" value="{{ old('anio', date('Y')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha del acta</label>
                    <input type="date" name="fecha_acta" class="form-control" value="{{ old('fecha_acta') }}" required>
                </div>
            </div>

            <h6 class="text-muted mb-3">PASO 3 — Datos específicos</h6>

            {{-- NACIMIENTO --}}
            <div class="seccion-tipo" data-tipo="NACIMIENTO">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label class="form-label">Nombres</label><input type="text" name="nombres" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido paterno</label><input type="text" name="apellido_paterno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido materno</label><input type="text" name="apellido_materno" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">DNI</label><input type="text" name="dni" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Hora de nacimiento</label><input type="time" name="hora_nacimiento" class="form-control"></div>
                    <div class="col-md-3">
                        <label class="form-label">Sexo</label>
                        <select name="sexo" class="form-select">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Lugar de nacimiento</label><input type="text" name="lugar_nacimiento" class="form-control"></div>
                    <div class="col-md-6"></div>
                    <div class="col-md-3"><label class="form-label">Nombre del padre</label><input type="text" name="nombre_padre" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">DNI del padre</label><input type="text" name="dni_padre" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Nombre de la madre</label><input type="text" name="nombre_madre" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">DNI de la madre</label><input type="text" name="dni_madre" class="form-control"></div>
                </div>
            </div>

            {{-- MATRIMONIO --}}
            <div class="seccion-tipo d-none" data-tipo="MATRIMONIO">
                <div class="row g-3 mb-2">
                    <div class="col-12"><strong>Contrayente 1</strong></div>
                    <div class="col-md-4"><label class="form-label">Nombres</label><input type="text" name="c1_nombres" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido paterno</label><input type="text" name="c1_apellido_paterno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido materno</label><input type="text" name="c1_apellido_materno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">DNI</label><input type="text" name="c1_dni" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input type="date" name="c1_fecha_nacimiento" class="form-control"></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12"><strong>Contrayente 2</strong></div>
                    <div class="col-md-4"><label class="form-label">Nombres</label><input type="text" name="c2_nombres" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido paterno</label><input type="text" name="c2_apellido_paterno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido materno</label><input type="text" name="c2_apellido_materno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">DNI</label><input type="text" name="c2_dni" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input type="date" name="c2_fecha_nacimiento" class="form-control"></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label class="form-label">Fecha de matrimonio</label><input type="date" name="fecha_matrimonio" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Lugar del matrimonio</label><input type="text" name="lugar_matrimonio" class="form-control"></div>
                </div>
            </div>

            {{-- DEFUNCION --}}
            <div class="seccion-tipo d-none" data-tipo="DEFUNCION">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label class="form-label">Nombres</label><input type="text" name="nombres" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido paterno</label><input type="text" name="apellido_paterno" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Apellido materno</label><input type="text" name="apellido_materno" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">DNI</label><input type="text" name="dni" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Fecha de defunción</label><input type="date" name="fecha_defuncion" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Lugar de defunción</label><input type="text" name="lugar_defuncion" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Causa de defunción</label><input type="text" name="causa_defuncion" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Médico que certifica</label><input type="text" name="medico_certifica" class="form-control"></div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones') }}</textarea>
            </div>

            <h6 class="text-muted mb-3">PASO 4 — Documento PDF escaneado</h6>
            <div class="mb-4">
                <input type="file" name="pdf" accept="application/pdf" class="form-control" required>
                <small class="text-muted">Solo archivos PDF. Se validará automáticamente que no esté dañado ni duplicado.</small>
            </div>

            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                Al guardar, el sistema validará: PDF válido, documento no dañado, número/año/tipo no duplicado
                y hash del documento no duplicado. Una vez guardada, el acta queda <strong>bloqueada</strong> y solo
                podrá consultarse, visualizarse e imprimirse.
            </div>

            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i> Guardar registro
            </button>
            <a href="{{ route('actas.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('input[name="tipo"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.seccion-tipo').forEach(function (seccion) {
            seccion.classList.toggle('d-none', seccion.dataset.tipo !== radio.value);
        });
    });
});
</script>
@endpush
