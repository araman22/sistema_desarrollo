<?php

namespace App\Policies;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class RolPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('roles.ver');
    }

    public function view(Usuario $usuario, Rol $rol): bool
    {
        return $usuario->tienePermiso('roles.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('roles.crear');
    }

    /**
     * El rol administrador se puede editar (descripción), pero su nombre y
     * su estado los protege UpdateRolRequest.
     */
    public function update(Usuario $usuario, Rol $rol): bool
    {
        return $usuario->tienePermiso('roles.editar');
    }

    /**
     * Sin $rol (al crear) solo se mira el permiso.
     */
    public function asignarPermisos(Usuario $usuario, ?Rol $rol = null): Response
    {
        if (! $usuario->tienePermiso('roles.asignar')) {
            return Response::deny('No tenés permiso para asignar permisos a los roles.');
        }

        if ($rol && self::esAdministrador($rol)) {
            return Response::deny('Los permisos del rol administrador no se pueden modificar.');
        }

        return Response::allow();
    }

    public function cambiarEstado(Usuario $usuario, Rol $rol): Response
    {
        if (! $usuario->tienePermiso('roles.editar')) {
            return Response::deny();
        }

        if (self::esAdministrador($rol)) {
            return Response::deny('El rol administrador no se puede desactivar.');
        }

        return Response::allow();
    }

    public function delete(Usuario $usuario, Rol $rol): Response
    {
        if (! $usuario->tienePermiso('roles.eliminar')) {
            return Response::deny();
        }

        if (self::esAdministrador($rol)) {
            return Response::deny('El rol administrador no se puede eliminar.');
        }

        if ($rol->usuarios()->exists()) {
            return Response::deny('El rol tiene usuarios asignados. Reasignalos antes de eliminarlo.');
        }

        return Response::allow();
    }

    public static function esAdministrador(Rol $rol): bool
    {
        return $rol->getOriginal('nombre') === Rol::ADMINISTRADOR;
    }
}
