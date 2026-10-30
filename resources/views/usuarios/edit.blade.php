@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="workspace-view view-form">
    <h1>Editar usuario {{ $usuario->nombre_usuario }}</h1>

    <form action="{{ route('usuarios.update', $usuario) }}" method="POST" class="record-form">
        @csrf
        @method('PUT')
        @include('usuarios._form')

        <div class="form-actions"><button type="submit" class="action-button action-primary">Guardar cambios</button>
        <a href="{{ route('usuarios.index') }}" class="action-button action-neutral">Cancelar</a></div>
    </form>
</div>
@endsection
