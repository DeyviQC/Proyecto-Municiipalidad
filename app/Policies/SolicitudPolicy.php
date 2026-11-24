<?php

namespace App\Policies;

use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    public function view(User $user, Solicitud $solicitud): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isAsistente() || ($user->isCiudadano() && $solicitud->usuario_id === $user->id));
    }

    public function update(User $user, Solicitud $solicitud): bool
    {
        return $user->is_active && $user->isCiudadano() && $solicitud->usuario_id === $user->id && $solicitud->estado === 'OBSERVADA';
    }

    public function atender(User $user, Solicitud $solicitud): bool
    {
        return $user->is_active && ($user->isAdmin() || $user->isAsistente());
    }
}
