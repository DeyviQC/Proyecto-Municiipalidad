@extends('layouts.app')
@section('titulo', 'Editar usuario')
@section('contenido')
<div class="card border-0 shadow-sm" style="max-width: 600px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nombre completo</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $usuario->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Usuario (username)</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', $usuario->username) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $usuario->email) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nueva contraseña (opcional)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar nueva contraseña</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select name="role" class="form-select" required>
                    <option value="administrador" {{ $usuario->isAdmin() ? 'selected' : '' }}>Administrador</option>
                    <option value="asistente" {{ $usuario->isAsistente() ? 'selected' : '' }}>Asistente</option>
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ $usuario->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Usuario activo</label>
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
