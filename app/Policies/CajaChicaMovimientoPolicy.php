<?php

namespace App\Policies;

use App\Models\CajaChicaMovimiento;
use App\Models\Usuario;

class CajaChicaMovimientoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('caja_chica.ver');
    }

    public function view(Usuario $usuario, CajaChicaMovimiento $registro): bool
    {
        return $usuario->tienePermiso('caja_chica.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('caja_chica.crear');
    }

    public function download(Usuario $usuario, CajaChicaMovimiento $registro): bool
    {
        return $usuario->tienePermiso('caja_chica.descargar');
    }
}
