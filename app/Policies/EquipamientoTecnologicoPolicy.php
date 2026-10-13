<?php

namespace App\Policies;

use App\Models\EquipamientoTecnologico;
use App\Models\Usuario;

class EquipamientoTecnologicoPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('equipamientos.ver');
    }

    public function view(Usuario $user, EquipamientoTecnologico $equipamientoTecnologico): bool
    {
        return $user->tienePermiso('equipamientos.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('equipamientos.crear');
    }

    public function update(Usuario $user, EquipamientoTecnologico $equipamientoTecnologico): bool
    {
        return $user->tienePermiso('equipamientos.editar');
    }

    public function delete(Usuario $user, EquipamientoTecnologico $equipamientoTecnologico): bool
    {
        return $user->tienePermiso('equipamientos.eliminar');
    }
}
