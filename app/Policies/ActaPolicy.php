<?php

namespace App\Policies;

use App\Models\Acta;
use App\Models\User;

/**
 * REGLA FUNDAMENTAL: las actas son inmutables (Sección 5).
 * update y delete devuelven false SIEMPRE, sin excepción, sin importar
 * el rol. No existe ruta ni interfaz para editar/eliminar un acta.
 */
class ActaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isAsistente());
    }

    public function view(User $user, Acta $acta): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isAsistente());
    }

    public function create(User $user): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isAsistente());
    }

    public function update(User $user, Acta $acta): bool
    {
        return false; // Inmutable. Sin excepciones.
    }

    public function delete(User $user, Acta $acta): bool
    {
        return false; // Inmutable. Sin excepciones.
    }
}
