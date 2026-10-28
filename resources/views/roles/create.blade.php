@extends('layouts.app')

@section('title', 'Nuevo rol')

@section('content')
<div class="workspace-view view-form">
    <h1>Nuevo rol</h1>

    <form action="{{ route('roles.store') }}" method="POST" class="record-form">
        @csrf
        @include('roles._form', ['rol' => null])

        <div class="form-actions"><button type="submit" class="action-button action-primary">Guardar</button>
        <a href="{{ route('roles.index') }}" class="action-button action-neutral">Cancelar</a></div>
    </form>
</div>
@endsection
