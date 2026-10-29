@can('view', $usuario)
<a class="action-button action-view" href="{{ route('usuarios.show', $usuario) }}">Ver</a>
@endcan
@can('update', $usuario)
    <a href="{{ route('usuarios.edit', $usuario) }}" class="action-button action-edit">Editar</a>
@endcan

@can('cambiarEstado', $usuario)
    <form action="{{ route('usuarios.estado', $usuario) }}" method="POST" style="display: inline" class="record-action-form">
        @csrf
        @method('PATCH')
        @if ($usuario->activo)
            <button type="submit" onclick="return confirm('¿Desactivar este usuario? No podrá iniciar sesión.')" class="action-button action-warning">Desactivar</button>
        @else
            <button type="submit" class="action-button action-view">Activar</button>
        @endif
    </form>
@endcan

@can('delete', $usuario)
    <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" style="display: inline" class="record-action-form">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')" class="action-button action-delete">Eliminar</button>
    </form>
@endcan
