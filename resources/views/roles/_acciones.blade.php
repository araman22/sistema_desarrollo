@can('view', $rol)
<a class="action-button action-view" href="{{ route('roles.show', $rol) }}">Ver</a>
@endcan
@can('update', $rol)
    <a href="{{ route('roles.edit', $rol) }}" class="action-button action-edit">Editar</a>
@endcan

@can('cambiarEstado', $rol)
    <form action="{{ route('roles.estado', $rol) }}" method="POST" style="display: inline" class="record-action-form">
        @csrf
        @method('PATCH')
        @if ($rol->activo)
            <button type="submit" onclick="return confirm('¿Desactivar este rol? Sus usuarios perderán los permisos del rol.')" class="action-button action-warning">Desactivar</button>
        @else
            <button type="submit" class="action-button action-view">Activar</button>
        @endif
    </form>
@endcan

@can('delete', $rol)
    <form action="{{ route('roles.destroy', $rol) }}" method="POST" style="display: inline" class="record-action-form">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('¿Eliminar este rol? Esta acción no se puede deshacer.')" class="action-button action-delete">Eliminar</button>
    </form>
@endcan
