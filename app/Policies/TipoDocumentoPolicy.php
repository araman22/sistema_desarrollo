<?php

namespace App\Policies;

use App\Models\TipoDocumento;
use App\Models\Usuario;

class TipoDocumentoPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->tienePermiso('documentos.ver');
    }

    public function view(Usuario $user, TipoDocumento $tipoDocumento): bool
    {
        return $user->tienePermiso('documentos.ver');
    }

    public function create(Usuario $user): bool
    {
        return $user->tienePermiso('documentos.crear');
    }

    public function update(Usuario $user, TipoDocumento $tipoDocumento): bool
    {
        return $user->tienePermiso('documentos.editar');
    }

    public function delete(Usuario $user, TipoDocumento $tipoDocumento): bool
    {
        return $user->tienePermiso('documentos.eliminar');
    }
}
