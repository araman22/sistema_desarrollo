@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
    <h1>Nuevo usuario</h1>

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf
        @include('usuarios._form', ['usuario' => null])

        <button type="submit">Guardar</button>
        <a href="{{ route('usuarios.index') }}">Cancelar</a>
    </form>
@endsection
