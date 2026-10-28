<?php

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Contracts\Auth\Authenticatable;

/** Único criterio de permisos de movilidad: vehículos, drones, baterías y sus mantenimientos. */
class PermisosMovilidad
{
    public static function permite(?Authenticatable $usuario, string $permiso): bool
    {
        // nombresPermisos() ya devuelve vacío si el rol está inactivo.
        return $usuario instanceof Usuario && $usuario->estaActivo() && $usuario->tienePermiso($permiso);
    }
}
