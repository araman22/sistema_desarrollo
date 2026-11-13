<?php

namespace App\Policies;

use App\Models\VehiculoMantenimiento;
use App\Support\PermisosMovilidad;
use Illuminate\Contracts\Auth\Authenticatable;

class VehiculoMantenimientoPolicy
{
    public function viewAny(Authenticatable $u): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculo_mantenimientos.ver');
    }

    public function view(Authenticatable $u, VehiculoMantenimiento $m): bool
    {
        return $this->viewAny($u);
    }

    public function create(Authenticatable $u): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculo_mantenimientos.crear');
    }

    public function update(Authenticatable $u, VehiculoMantenimiento $m): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculo_mantenimientos.editar');
    }

    public function delete(Authenticatable $u, VehiculoMantenimiento $m): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculo_mantenimientos.eliminar');
    }
}
