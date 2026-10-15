<?php

namespace App\Policies;

use App\Models\DroneRobot;
use App\Models\Usuario;
use App\Support\PermisosMovilidad as Permisos;

class DroneRobotPolicy
{
    public function viewAny(Usuario $u): bool
    {
        return Permisos::permite($u, 'drones_robots.ver');
    }

    public function view(Usuario $u, DroneRobot $r): bool
    {
        return $this->viewAny($u);
    }

    public function create(Usuario $u): bool
    {
        return Permisos::permite($u, 'drones_robots.crear');
    }

    public function update(Usuario $u, DroneRobot $r): bool
    {
        return Permisos::permite($u, 'drones_robots.editar');
    }

    public function delete(Usuario $u, DroneRobot $r): bool
    {
        return Permisos::permite($u, 'drones_robots.eliminar');
    }
}
