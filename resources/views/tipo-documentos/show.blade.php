@extends('layouts.app')

@section('title', 'Detalle de tipo de documento')

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $tipoDocumento->nombre }}</h1>
            <p class="muted">Catálogo documentario del sistema.</p>
        </div>
        @can('update', $tipoDocumento)
            <a href="{{ route('tipo-documentos.edit', $tipoDocumento) }}" class="btn">Editar</a>
        @endcan
    </div>

    <div class="row" style="margin-bottom: 20px;">
        <span class="badge">{{ $tipoDocumento->documentos()->count() }} documentos</span>
    </div>

    <div class="row">
        <a href="{{ route('tipo-documentos.index') }}" class="btn secondary">Volver al listado</a>
    </div>
</div>
@endsection
