<?php

namespace App\Policies;

use App\Models\Alerta;
use App\Models\Usuario;

class AlertaPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('alertas.ver');
    }

    public function view(Usuario $usuario, Alerta $registro): bool
    {
        return $usuario->tienePermiso('alertas.ver')
            && $registro->usuario_id === $usuario->id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('alertas.crear');
    }

    public function update(Usuario $usuario, Alerta $registro): bool
    {
        return $usuario->tienePermiso('alertas.editar')
            && $registro->usuario_id === $usuario->id;
    }

    public function delete(Usuario $usuario, Alerta $registro): bool
    {
        return $usuario->tienePermiso('alertas.eliminar')
            && $registro->usuario_id === $usuario->id;
    }

    public function atender(Usuario $usuario, Alerta $registro): bool
    {
        return $usuario->tienePermiso('alertas.atender')
            && $registro->usuario_id === $usuario->id;
    }
}
