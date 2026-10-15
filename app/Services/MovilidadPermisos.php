<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class MovilidadPermisos
{
    public static function permite(Authenticatable $user, string $permiso): bool
    {
        if (method_exists($user, 'tienePermiso')) {
            return $user->estaActivo() && $user->tienePermiso($permiso);
        }
        if (! ($user instanceof User)) {
            return false;
        }
        $acceso = DB::table('movilidad_accesos')->where('user_id', $user->getAuthIdentifier())->first();

        return $acceso && (str_ends_with($permiso, '.ver') || $acceso->puede_editar);
    }
}
