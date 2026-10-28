@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="workspace-view view-index">
    <div class="page-header"><h1>Roles</h1>

    @can('create', App\Models\Rol::class)
        <p><a href="{{ route('roles.create') }}" class="action-button action-primary">Nuevo rol</a></p>
    @endcan</div>

    <form action="{{ route('roles.index') }}" method="GET" class="panel filter-panel">
        <div class="filter-field"><label for="q">Buscar:</label>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Nombre del rol"></div>
        <button type="submit" class="action-button action-primary">Buscar</button>
        <a href="{{ route('roles.index') }}" class="action-button action-neutral">Limpiar</a>
    </form>

    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Usuarios</th>
                <th>Permisos</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roles as $rol)
                <tr>
                    <td>{{ $rol->nombre }}</td>
                    <td>{{ $rol->descripcion ?? '—' }}</td>
                    <td>{{ $rol->activo ? 'Activo' : 'Inactivo' }}</td>
                    <td>{{ $rol->usuarios_count }}</td>
                    <td>{{ $rol->permisos_count }}</td>
                    <td>
                        @include('roles._acciones', ['rol' => $rol])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay roles que coincidan con la búsqueda.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>

    {{ $roles->links() }}
</div>
@endsection
