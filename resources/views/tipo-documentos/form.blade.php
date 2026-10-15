@extends('layouts.app')

@section('title', isset($tipoDocumento->id) ? 'Editar tipo de documento' : 'Nuevo tipo de documento')

@section('content')
<div class="panel" style="max-width: 520px; margin: 0 auto;">
    <h1>{{ isset($tipoDocumento->id) ? 'Editar tipo de documento' : 'Nuevo tipo de documento' }}</h1>
    <form method="POST" action="{{ isset($tipoDocumento->id) ? route('tipo-documentos.update', $tipoDocumento) : route('tipo-documentos.store') }}">
        @csrf
        @if(isset($tipoDocumento->id))
            @method('PUT')
        @endif

        <div class="field">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $tipoDocumento->nombre) }}" required>
        </div>

        <div class="row">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ route('tipo-documentos.index') }}" class="btn secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
