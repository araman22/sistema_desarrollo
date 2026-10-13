<?php

namespace App\Policies;

use App\Models\Inventario;
use App\Models\Usuario;

class InventarioPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('inventarios.ver');
    }

    public function view(Usuario $user, Inventario $inventario): bool
    {
        return $user->tienePermiso('inventarios.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('inventarios.crear');
    }

    public function update(Usuario $user, Inventario $inventario): bool
    {
        return $user->tienePermiso('inventarios.editar');
    }

    public function delete(Usuario $user, Inventario $inventario): bool
    {
        return $user->tienePermiso('inventarios.eliminar');
    }
}
