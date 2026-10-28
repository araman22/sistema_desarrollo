<?php

namespace App\Policies;

use App\Models\Expediente;
use App\Models\Usuario;

class ExpedientePolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('expedientes.ver');
    }

    public function view(Usuario $user, Expediente $expediente): bool
    {
        return $user->tienePermiso('expedientes.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('expedientes.crear');
    }

    public function update(Usuario $user, Expediente $expediente): bool
    {
        return $user->tienePermiso('expedientes.editar');
    }

    // Registrar envíos y recepciones de pases.
    public function pasar(Usuario $user, Expediente $expediente): bool
    {
        return $user->tienePermiso('expedientes.pasar');
    }

    // El controlador además impide borrar expedientes con pases, para conservar su recorrido.
    public function delete(Usuario $user, Expediente $expediente): bool
    {
        return $user->tienePermiso('expedientes.eliminar');
    }

    // Alta y edición de biblioratos o carpetas.
    public function categorias(Usuario $user): bool
    {
        return $user->tienePermiso('expedientes.categorias');
    }
}
