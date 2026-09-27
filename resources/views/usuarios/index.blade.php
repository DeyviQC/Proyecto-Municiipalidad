@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
<div class="d-flex justify-content-between mb-3">
    <h5 class="mb-0">USUARIOS</h5>
    <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> Nuevo usuario
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Usuario</th><th>Nombre</th><th>Rol</th><th>Estado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->username }}</td>
                        <td>{{ $usuario->name }}</td>
                        <td>
                            @foreach ($usuario->roles as $rol)
                                <span class="badge text-bg-info-subtle text-info-emphasis">{{ $rol->nombre }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if ($usuario->is_active)
                                <span class="badge text-bg-success">Activo</span>
                            @else
                                <span class="badge text-bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if ($usuario->id !== auth()->id())
                                <form method="POST" action="{{ route('usuarios.alternar-estado', $usuario) }}" class="d-inline"
                                      data-confirm="¿Confirma cambiar el estado de este usuario?">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-{{ $usuario->is_active ? 'secondary' : 'success' }}">
                                        <i class="bi bi-{{ $usuario->is_active ? 'slash-circle' : 'check-circle' }}"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $usuarios->links() }}</div>
</div>
@endsection
