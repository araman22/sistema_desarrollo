@extends('layouts.app')

@section('title', 'Editar rol')

@section('content')
<div class="workspace-view view-form">
    <h1>Editar rol {{ $rol->nombre }}</h1>

    <form action="{{ route('roles.update', $rol) }}" method="POST" class="record-form">
        @csrf
        @method('PUT')
        @include('roles._form')

        <div class="form-actions"><button type="submit" class="action-button action-primary">Guardar cambios</button>
        <a href="{{ route('roles.index') }}" class="action-button action-neutral">Cancelar</a></div>
    </form>
</div>
@endsection
