@extends('layouts.app')

@section('title', 'Detalle del documento')

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $documento->nombre }}</h1>
            <p class="muted">{{ $documento->tipo?->nombre ?? 'Sin tipo' }} · Nº {{ $documento->numero }}</p>
        </div>
        @can('update', $documento)
            <a href="{{ route('documentos.edit', $documento) }}" class="btn">Editar</a>
        @endcan
    </div>

    <div class="grid">
        <div class="card"><strong>Tipo:</strong> {{ $documento->tipo?->nombre ?? 'Sin tipo' }}</div>
        <div class="card"><strong>Archivo:</strong> {{ $documento->archivo_original ?? 'Sin archivo' }}</div>
        <div class="card"><strong>Tamaño:</strong> {{ $documento->tamano_bytes ? number_format($documento->tamano_bytes / 1024, 1) . ' KB' : '—' }}</div>
        <div class="card"><strong>Subido por:</strong> {{ $documento->usuario?->nombre_usuario ?? 'Sistema' }}</div>
    </div>

    <div class="field" style="margin-top:20px;">
        <label>Descripción</label>
        <p>{{ $documento->descripcion }}</p>
    </div>

    <div class="row">
        @can('download', $documento)
            <a href="{{ route('documentos.download', $documento) }}" class="btn">Descargar archivo</a>
        @endcan
        <a href="{{ route('documentos.index') }}" class="btn secondary">Volver al listado</a>
    </div>
</div>
@endsection
