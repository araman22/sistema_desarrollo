<?php

namespace App\Policies;

use App\Models\FondoFijoMovimiento;
use App\Models\Usuario;

class FondoFijoMovimientoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('fondo_fijo.ver');
    }

    public function view(Usuario $usuario, FondoFijoMovimiento $registro): bool
    {
        return $usuario->tienePermiso('fondo_fijo.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('fondo_fijo.crear');
    }

    public function download(Usuario $usuario, FondoFijoMovimiento $registro): bool
    {
        return $usuario->tienePermiso('fondo_fijo.descargar');
    }
}
