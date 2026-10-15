<?php

namespace App\Policies;

use App\Models\Auditoria;
use App\Models\Usuario;

class AuditoriaPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('auditorias.ver');
    }

    public function view(Usuario $usuario, Auditoria $registro): bool
    {
        return $usuario->tienePermiso('auditorias.ver');
    }

    public function export(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('auditorias.exportar');
    }

    public function create(Usuario $usuario): bool
    {
        return false;
    }

    public function update(Usuario $usuario, Auditoria $registro): bool
    {
        return false;
    }

    public function delete(Usuario $usuario, Auditoria $registro): bool
    {
        return false;
    }
}
