<?php

namespace App\Policies;

use App\Models\Licencia;
use App\Models\Usuario;

class LicenciaPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('licencias.ver');
    }

    public function view(Usuario $user, Licencia $licencia): bool
    {
        return $user->tienePermiso('licencias.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('licencias.crear');
    }

    public function update(Usuario $user, Licencia $licencia): bool
    {
        return $user->tienePermiso('licencias.editar');
    }

    // Autorizar o rechazar es una decisión separada de la carga.
    public function autorizar(Usuario $user): bool
    {
        return $user->tienePermiso('licencias.autorizar');
    }

    public function delete(Usuario $user, Licencia $licencia): bool
    {
        return $licencia->estado === 'pendiente' && $user->tienePermiso('licencias.eliminar');
    }
}
