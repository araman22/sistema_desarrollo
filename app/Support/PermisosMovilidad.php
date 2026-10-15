<?php

namespace App\Support;

use App\Models\Usuario;

class PermisosMovilidad
{
    public static function permite(Usuario $usuario, string $permiso): bool
    {
        return $usuario->estaActivo() && $usuario->rol?->activo && $usuario->tienePermiso($permiso);
    }
}
