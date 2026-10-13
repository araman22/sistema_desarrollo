@extends('layouts.app')

@section('title', 'Nuevo rol')

@section('content')
    <h1>Nuevo rol</h1>

    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        @include('roles._form', ['rol' => null])

        <button type="submit">Guardar</button>
        <a href="{{ route('roles.index') }}">Cancelar</a>
    </form>
@endsection
