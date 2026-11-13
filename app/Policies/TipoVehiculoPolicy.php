<?php

namespace App\Policies;

use App\Models\TipoVehiculo;
use App\Support\PermisosMovilidad;
use Illuminate\Contracts\Auth\Authenticatable;

// El catálogo de tipos tiene permisos propios, separados de los de vehículos.
class TipoVehiculoPolicy
{
    public function viewAny(Authenticatable $u): bool
    {
        return PermisosMovilidad::permite($u, 'tipos_vehiculos.ver');
    }

    public function view(Authenticatable $u, TipoVehiculo $m): bool
    {
        return $this->viewAny($u);
    }

    public function create(Authenticatable $u): bool
    {
        return PermisosMovilidad::permite($u, 'tipos_vehiculos.crear');
    }

    public function update(Authenticatable $u, TipoVehiculo $m): bool
    {
        return PermisosMovilidad::permite($u, 'tipos_vehiculos.editar');
    }

    public function delete(Authenticatable $u, TipoVehiculo $m): bool
    {
        return PermisosMovilidad::permite($u, 'tipos_vehiculos.eliminar');
    }
}
