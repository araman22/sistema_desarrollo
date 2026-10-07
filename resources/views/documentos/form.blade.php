@extends('layouts.app')

@section('title', isset($documento->id) ? 'Editar documento' : 'Nuevo documento')

@section('content')
<div class="panel" style="max-width: 720px; margin: 0 auto;">
    <h1>{{ isset($documento->id) ? 'Editar documento' : 'Nuevo documento' }}</h1>
    <form method="POST" action="{{ isset($documento->id) ? route('documentos.update', $documento) : route('documentos.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($documento->id))
            @method('PUT')
        @endif

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="tipo_documento_id">Tipo</label>
                <select id="tipo_documento_id" name="tipo_documento_id" required>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}" {{ old('tipo_documento_id', $documento->tipo_documento_id) == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="numero">Número</label>
                <input id="numero" name="numero" type="number" min="1" value="{{ old('numero', $documento->numero) }}" required>
            </div>
        </div>

        <div class="field">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $documento->nombre) }}" required>
        </div>

        <div class="field">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" required>{{ old('descripcion', $documento->descripcion) }}</textarea>
        </div>

        <div class="field">
            <label for="archivo">Archivo (máx. 5 MB, PDF/DOC/DOCX/XLS/XLSX/JPG/PNG/TXT)</label>
            <input id="archivo" name="archivo" type="file">
            @if($documento->ruta_archivo)
                <p class="small muted">Archivo actual: {{ $documento->archivo_original }}</p>
            @endif
        </div>

        <div class="row">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ route('documentos.index') }}" class="btn secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
