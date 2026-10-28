<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Contracts\Auth\Authenticatable;

class MovilidadPermisos
{
    public static function permite(Authenticatable $user, string $permiso): bool
    {
        return $user instanceof Usuario && $user->estaActivo() && $user->tienePermiso($permiso);
    }
}
