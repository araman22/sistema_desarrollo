<?php

namespace App\Policies;

use App\Models\Bateria;
use App\Models\Usuario;
use App\Support\PermisosMovilidad as Permisos;

class BateriaPolicy
{
    private function permite(Usuario $u, string $accion): bool
    {
        return Permisos::permite($u, 'baterias.'.$accion) && Permisos::permite($u, 'drones_robots.ver');
    }

    public function viewAny(Usuario $u): bool
    {
        return $this->permite($u, 'ver');
    }

    public function view(Usuario $u, Bateria $r): bool
    {
        return $this->viewAny($u);
    }

    public function create(Usuario $u): bool
    {
        return $this->permite($u, 'crear');
    }

    public function update(Usuario $u, Bateria $r): bool
    {
        return $this->permite($u, 'editar');
    }

    public function delete(Usuario $u, Bateria $r): bool
    {
        return $this->permite($u, 'eliminar');
    }
}
