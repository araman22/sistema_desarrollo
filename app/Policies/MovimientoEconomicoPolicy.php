<?php

namespace App\Policies;

use App\Models\MovimientoEconomico;
use App\Models\Usuario;

class MovimientoEconomicoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('movimientos_economicos.ver');
    }

    public function view(Usuario $usuario, MovimientoEconomico $registro): bool
    {
        return $usuario->tienePermiso('movimientos_economicos.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('movimientos_economicos.crear');
    }

    public function update(Usuario $usuario, MovimientoEconomico $registro): bool
    {
        return $usuario->tienePermiso('movimientos_economicos.editar');
    }

    public function delete(Usuario $usuario, MovimientoEconomico $registro): bool
    {
        return $usuario->tienePermiso('movimientos_economicos.eliminar');
    }

    public function export(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('movimientos_economicos.exportar');
    }
}
