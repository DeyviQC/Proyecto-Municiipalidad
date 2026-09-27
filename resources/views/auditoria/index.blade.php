@extends('layouts.app')

@section('titulo', 'Auditoría')

@section('contenido')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('auditoria.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach (['LOGIN','LOGOUT','LOGIN_FALLIDO','CREAR_ACTA','CONSULTAR_ACTA','VER_PDF','IMPRIMIR_PDF','SUBIR_PDF','INTENTO_DUPLICADO','CREAR_USUARIO','EDITAR_USUARIO','DESACTIVAR_USUARIO','IMPORTACION_MASIVA'] as $accion)
                        <option value="{{ $accion }}" {{ ($filtros['accion'] ?? '') === $accion ? 'selected' : '' }}>{{ $accion }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Módulo</label>
                <input type="text" name="modulo" class="form-control form-control-sm" value="{{ $filtros['modulo'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ $filtros['fecha_desde'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ $filtros['fecha_hasta'] ?? '' }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Módulo</th><th>Descripción</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr>
                        <td class="text-nowrap">{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $registro->usuario->name ?? 'Sistema' }}</td>
                        <td><span class="badge text-bg-light border">{{ $registro->accion }}</span></td>
                        <td>{{ $registro->modulo }}</td>
                        <td class="small">{{ $registro->descripcion }}</td>
                        <td class="small text-muted">{{ $registro->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sin registros de auditoría.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $registros->links() }}</div>
</div>
@endsection
