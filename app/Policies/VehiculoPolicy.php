<?php

namespace App\Policies;

use App\Models\Vehiculo;
use App\Support\PermisosMovilidad;
use Illuminate\Contracts\Auth\Authenticatable;

class VehiculoPolicy
{
    public function viewAny(Authenticatable $u): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculos.ver');
    }

    public function view(Authenticatable $u, Vehiculo $m): bool
    {
        return $this->viewAny($u);
    }

    public function create(Authenticatable $u): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculos.crear');
    }

    public function update(Authenticatable $u, Vehiculo $m): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculos.editar');
    }

    public function delete(Authenticatable $u, Vehiculo $m): bool
    {
        return PermisosMovilidad::permite($u, 'vehiculos.eliminar');
    }
}
