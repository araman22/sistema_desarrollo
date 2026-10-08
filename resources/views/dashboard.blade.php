@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
    <h1>Bienvenido, {{ auth()->user()->nombre_usuario }}</h1>

    @can('viewAny', App\Models\Usuario::class)
        <p><a href="{{ route('usuarios.index') }}">Usuarios</a></p>
    @endcan
@endsection
