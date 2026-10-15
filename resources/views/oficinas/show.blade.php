@extends('layouts.app')

@section('title', 'Detalle de oficina')

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $oficina->nombre }}</h1>
            <p class="muted">{{ $oficina->ubicacion ?? 'Sin ubicación definida' }}</p>
        </div>
        @can('update', $oficina)
            <a href="{{ route('oficinas.edit', $oficina) }}" class="btn">Editar</a>
        @endcan
    </div>

    <div class="grid">
        <div class="card"><strong>Estado:</strong> {{ $oficina->estado }}</div>
        <div class="card"><strong>Responsable:</strong> {{ $oficina->responsable?->persona?->nombre_completo ?? 'No asignado' }}</div>
        <div class="card"><strong>Teléfono:</strong> {{ $oficina->telefono ?? '—' }}</div>
        <div class="card"><strong>Correo:</strong> {{ $oficina->correo_electronico ?? '—' }}</div>
    </div>

    <div class="field" style="margin-top:20px;">
        <label>Descripción</label>
        <p>{{ $oficina->descripcion ?? 'Sin descripción' }}</p>
    </div>

    <div class="field">
        <label>Observaciones</label>
        <p>{{ $oficina->observaciones ?? 'Sin observaciones' }}</p>
    </div>

    <div class="row">
        <a href="{{ route('oficinas.index') }}" class="btn secondary">Volver al listado</a>
    </div>
</div>
@endsection
