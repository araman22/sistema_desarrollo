@extends('layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
<div class="workspace-view view-form">
    <h1>Nuevo usuario</h1>

    <form action="{{ route('usuarios.store') }}" method="POST" class="record-form">
        @csrf
        @include('usuarios._form', ['usuario' => null])

        <div class="form-actions"><button type="submit" class="action-button action-primary">Guardar</button>
        <a href="{{ route('usuarios.index') }}" class="action-button action-neutral">Cancelar</a></div>
    </form>
</div>
@endsection
