@extends('layouts.app')

@section('title', 'Usuario '.$usuario->nombre_usuario)

@section('content')
    <h1>Usuario {{ $usuario->nombre_usuario }}</h1>

    <dl>
        <dt>Nombre de usuario</dt>
        <dd>{{ $usuario->nombre_usuario }}</dd>

        <dt>Correo electrónico</dt>
        <dd>{{ $usuario->correo_electronico ?? '—' }}</dd>

        <dt>Rol</dt>
        <dd>{{ $usuario->rol?->nombre ?? '—' }}</dd>

        <dt>Estado</dt>
        <dd>{{ $usuario->activo ? 'Activo' : 'Inactivo' }}</dd>

        <dt>Policía asociado</dt>
        <dd>
            @if ($usuario->policia)
                {{ $usuario->policia->numero_legajo }} — {{ $usuario->policia->nombre_completo }}
            @else
                Ninguno
            @endif
        </dd>

        <dt>Último acceso</dt>
        <dd>{{ $usuario->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca' }}</dd>

        <dt>Creado</dt>
        <dd>{{ $usuario->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
    </dl>

    <p>
        @include('usuarios._acciones', ['usuario' => $usuario])
        <a href="{{ route('usuarios.index') }}">Volver al listado</a>
    </p>
@endsection
