@extends('layouts.app')

@section('title', isset($tipoDocumento->id) ? 'Editar tipo de documento' : 'Nuevo tipo de documento')

@section('content')
<div class="workspace-view view-form">
<div class="panel page-header" style="max-width: 520px; margin: 0 auto;">
    <h1>{{ isset($tipoDocumento->id) ? 'Editar tipo de documento' : 'Nuevo tipo de documento' }}</h1>
    <form method="POST" action="{{ isset($tipoDocumento->id) ? route('tipo-documentos.update', $tipoDocumento) : route('tipo-documentos.store') }}" class="record-form">
        @csrf
        @if(isset($tipoDocumento->id))
            @method('PUT')
        @endif

        <div class="field">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $tipoDocumento->nombre) }}" required>
        </div>

        <div class="row form-actions">
            <button type="submit" class="action-button action-primary">Guardar</button>
            <a href="{{ route('tipo-documentos.index') }}" class="action-button action-neutral">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
