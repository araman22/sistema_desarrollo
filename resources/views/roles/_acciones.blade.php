@can('update', $rol)
    <a href="{{ route('roles.edit', $rol) }}">Editar</a>
@endcan

@can('cambiarEstado', $rol)
    <form action="{{ route('roles.estado', $rol) }}" method="POST" style="display: inline">
        @csrf
        @method('PATCH')
        @if ($rol->activo)
            <button type="submit" onclick="return confirm('¿Desactivar este rol? Sus usuarios perderán los permisos del rol.')">Desactivar</button>
        @else
            <button type="submit">Activar</button>
        @endif
    </form>
@endcan

@can('delete', $rol)
    <form action="{{ route('roles.destroy', $rol) }}" method="POST" style="display: inline">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('¿Eliminar este rol? Esta acción no se puede deshacer.')">Eliminar</button>
    </form>
@endcan
