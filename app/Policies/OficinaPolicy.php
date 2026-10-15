<?php

namespace App\Policies;

use App\Models\Oficina;
use App\Models\Usuario;

class OficinaPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('oficinas.ver');
    }

    public function view(Usuario $user, Oficina $oficina): bool
    {
        return $user->tienePermiso('oficinas.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('oficinas.crear');
    }

    public function update(Usuario $user, Oficina $oficina): bool
    {
        return $user->tienePermiso('oficinas.editar');
    }

    public function delete(Usuario $user, Oficina $oficina): bool
    {
        return $user->tienePermiso('oficinas.eliminar');
    }
}
