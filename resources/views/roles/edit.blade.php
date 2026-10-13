@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
    <h1>Editar rol {{ $rol->nombre }}</h1>

    <form action="{{ route('roles.update', $rol) }}" method="POST">
        @csrf
        @method('PUT')
        @include('roles._form')

        <button type="submit">Guardar cambios</button>
        <a href="{{ route('roles.index') }}">Cancelar</a>
    </form>
@endsection
