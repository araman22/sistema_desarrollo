@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <h1>Roles</h1>

    @can('create', App\Models\Rol::class)
        <p><a href="{{ route('roles.create') }}">Nuevo rol</a></p>
    @endcan

    <form action="{{ route('roles.index') }}" method="GET">
        <label for="q">Buscar:</label>
        <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Nombre del rol">
        <button type="submit">Buscar</button>
        <a href="{{ route('roles.index') }}">Limpiar</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Usuarios</th>
                <th>Permisos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($roles as $rol)
                <tr>
                    <td><a href="{{ route('roles.show', $rol) }}">{{ $rol->nombre }}</a></td>
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
    </table>

    {{ $roles->links() }}
@endsection
