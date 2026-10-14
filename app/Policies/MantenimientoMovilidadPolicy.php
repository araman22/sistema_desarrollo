<?php

namespace App\Policies;

use App\Models\Bateria;
use App\Models\DroneRobot;
use App\Models\Mantenimiento;
use App\Models\Usuario;
use App\Support\PermisosMovilidad as Permisos;
use Illuminate\Database\Eloquent\Model;

class MantenimientoMovilidadPolicy
{
    private function permite(Usuario $u, string $accion, Model $recurso): bool
    {
        return ($recurso instanceof DroneRobot || $recurso instanceof Bateria)
            && Permisos::permite($u, 'mantenimientos.'.$accion) && $u->can('view', $recurso);
    }

    public function viewAny(Usuario $u, Model $recurso): bool
    {
        return $this->permite($u, 'ver', $recurso);
    }

    public function view(Usuario $u, Mantenimiento $m): bool
    {
        return $m->mantenible !== null && $this->permite($u, 'ver', $m->mantenible);
    }

    public function create(Usuario $u, Model $recurso): bool
    {
        return $this->permite($u, 'crear', $recurso);
    }

    public function update(Usuario $u, Mantenimiento $m): bool
    {
        return ! in_array($m->estado, ['realizado', 'cancelado'], true) && $m->mantenible !== null && $this->permite($u, 'editar', $m->mantenible);
    }

    public function delete(Usuario $u, Mantenimiento $m): bool
    {
        return $m->mantenible !== null && $this->permite($u, 'eliminar', $m->mantenible) && $m->estado === 'programado';
    }
}
