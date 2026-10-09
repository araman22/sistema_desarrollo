<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\Usuario;

class DocumentoPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('documentos.ver');
    }

    public function view(Usuario $user, Documento $documento): bool
    {
        return $user->tienePermiso('documentos.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('documentos.crear');
    }

    public function update(Usuario $user, Documento $documento): bool
    {
        return $user->tienePermiso('documentos.editar');
    }

    public function delete(Usuario $user, Documento $documento): bool
    {
        return $user->tienePermiso('documentos.eliminar');
    }

    public function download(Usuario $user, Documento $documento): bool
    {
        return $user->tienePermiso('documentos.descargar');
    }
}
