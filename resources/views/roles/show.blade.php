@extends('layouts.app')

@section('title', 'Rol '.$rol->nombre)

@section('content')
    <h1>Rol {{ $rol->nombre }}</h1>

    <dl>
        <dt>Descripción</dt>
        <dd>{{ $rol->descripcion ?? '—' }}</dd>

        <dt>Estado</dt>
        <dd>{{ $rol->activo ? 'Activo' : 'Inactivo' }}</dd>

        <dt>Usuarios asignados</dt>
        <dd>{{ $rol->usuarios_count }}</dd>
    </dl>

    <h2>Permisos</h2>
    @forelse ($permisosPorModulo as $modulo => $permisos)
        <p>
            <strong>{{ $modulo }}:</strong>
            {{ $permisos->pluck('accion')->join(', ') }}
        </p>
    @empty
        <p>El rol no tiene permisos asignados.</p>
    @endforelse

    <p>
        @include('roles._acciones', ['rol' => $rol])
        <a href="{{ route('roles.index') }}">Volver al listado</a>
    </p>
@endsection
