@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
    <h1>Editar usuario {{ $usuario->nombre_usuario }}</h1>

    <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
        @csrf
        @method('PUT')
        @include('usuarios._form')

        <button type="submit">Guardar cambios</button>
        <a href="{{ route('usuarios.index') }}">Cancelar</a>
    </form>
@endsection
