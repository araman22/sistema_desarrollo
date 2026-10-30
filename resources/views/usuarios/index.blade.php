@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="workspace-view view-index">
    <div class="page-header"><h1>Usuarios</h1>

    @can('create', App\Models\Usuario::class)
        <p><a href="{{ route('usuarios.create') }}" class="action-button action-primary">Nuevo usuario</a></p>
    @endcan</div>

    <form action="{{ route('usuarios.index') }}" method="GET" class="panel filter-panel">
        <div class="filter-field"><label for="q">Buscar:</label>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Usuario o correo"></div>

        <div class="filter-field"><label for="rol_id">Rol:</label>
        <select id="rol_id" name="rol_id">
            <option value="">Todos</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}" @selected((string) request('rol_id') === (string) $rol->id)>{{ $rol->nombre }}</option>
            @endforeach
        </select></div>

        <div class="filter-field"><label for="activo">Estado:</label>
        <select id="activo" name="activo">
            <option value="">Todos</option>
            <option value="1" @selected(request('activo') === '1')>Activos</option>
            <option value="0" @selected(request('activo') === '0')>Inactivos</option>
        </select></div>

        <button type="submit" class="action-button action-primary">Filtrar</button>
        <a href="{{ route('usuarios.index') }}" class="action-button action-neutral">Limpiar</a>
    </form>

    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Último acceso</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nombre_usuario }}</td>
                    <td>{{ $usuario->correo_electronico ?? '—' }}</td>
                    <td>{{ $usuario->rol?->nombre ?? '—' }}</td>
                    <td>{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
                    <td>
                        @include('usuarios._acciones', ['usuario' => $usuario])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay usuarios que coincidan con la búsqueda.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    {{ $usuarios->links() }}
</div>
@endsection
