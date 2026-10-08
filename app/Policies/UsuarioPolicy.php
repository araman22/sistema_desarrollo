<?php

namespace App\Policies;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class UsuarioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('usuarios.ver');
    }

    public function view(Usuario $usuario, Usuario $objetivo): bool
    {
        return $usuario->tienePermiso('usuarios.ver');
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->tienePermiso('usuarios.crear');
    }

    public function update(Usuario $usuario, Usuario $objetivo): bool
    {
        return $usuario->tienePermiso('usuarios.editar');
    }

    public function restablecerContrasena(Usuario $usuario, Usuario $objetivo): bool
    {
        return $usuario->tienePermiso('usuarios.restablecer');
    }

    /**
     * Activar o desactivar a otro usuario.
     */
    public function cambiarEstado(Usuario $usuario, Usuario $objetivo): Response
    {
        if (! $usuario->tienePermiso('usuarios.editar')) {
            return Response::deny();
        }

        if ($usuario->is($objetivo)) {
            return Response::deny('No podés activar ni desactivar tu propio usuario.');
        }

        if (self::esUltimoAdministradorActivo($objetivo)) {
            return Response::deny('No se puede desactivar al último administrador activo del sistema.');
        }

        return Response::allow();
    }

    public function delete(Usuario $usuario, Usuario $objetivo): Response
    {
        if (! $usuario->tienePermiso('usuarios.eliminar')) {
            return Response::deny();
        }

        if ($usuario->is($objetivo)) {
            return Response::deny('No podés eliminar tu propio usuario.');
        }

        if (self::esUltimoAdministradorActivo($objetivo)) {
            return Response::deny('No se puede eliminar al último administrador activo del sistema.');
        }

        return Response::allow();
    }

    /**
     * True si el usuario es administrador, está activo y no hay otro
     * administrador activo que pueda reemplazarlo.
     */
    public static function esUltimoAdministradorActivo(Usuario $objetivo): bool
    {
        if (! $objetivo->activo || ! $objetivo->tieneRol(Rol::ADMINISTRADOR)) {
            return false;
        }

        return ! Usuario::query()
            ->whereKeyNot($objetivo->getKey())
            ->where('activo', true)
            ->whereHas('rol', fn ($query) => $query->where('nombre', Rol::ADMINISTRADOR))
            ->exists();
    }
}
