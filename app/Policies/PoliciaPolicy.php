<?php

namespace App\Policies;

use App\Models\Policia;
use App\Models\Usuario;

class PoliciaPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('policias.ver');
    }

    public function view(Usuario $user, Policia $policia): bool
    {
        return $user->tienePermiso('policias.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('policias.crear');
    }

    // También habilita cargar familiares, funciones y novedades de revista.
    public function update(Usuario $user, Policia $policia): bool
    {
        return $user->tienePermiso('policias.editar');
    }

    public function delete(Usuario $user, Policia $policia): bool
    {
        return $user->tienePermiso('policias.eliminar');
    }
}
