@extends('layouts.app')

@section('title', 'Detalle de inventario')

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $inventario->codigo }}</h1>
            <p class="muted">{{ $inventario->descripcion }}</p>
        </div>
        @can('update', $inventario)
            <a href="{{ route('inventarios.edit', $inventario) }}" class="btn">Editar</a>
        @endcan
    </div>

    <div class="grid">
        <div class="card"><strong>Categoria:</strong> {{ $inventario->categoria }}</div>
        <div class="card"><strong>Estado:</strong> {{ $inventario->estado }}</div>
        <div class="card"><strong>Oficina:</strong> {{ $inventario->oficina->nombre ?? 'Sin oficina' }}</div>
        <div class="card"><strong>Responsable:</strong> {{ $inventario->responsable?->persona?->nombre_completo ?? 'Sin responsable' }}</div>
        <div class="card"><strong>Marca:</strong> {{ $inventario->marca ?? '—' }}</div>
        <div class="card"><strong>Modelo:</strong> {{ $inventario->modelo ?? '—' }}</div>
        <div class="card"><strong>Serie:</strong> {{ $inventario->numero_serie ?? '—' }}</div>
        <div class="card"><strong>Valor:</strong> ${{ number_format((float) ($inventario->valor_adquisicion ?? 0), 2, ',', '.') }}</div>
    </div>

    <div class="field" style="margin-top:20px;">
        <label>Ubicación</label>
        <p>{{ $inventario->ubicacion ?? 'Sin ubicación' }}</p>
    </div>

    <div class="field">
        <label>Observaciones</label>
        <p>{{ $inventario->observaciones ?? 'Sin observaciones' }}</p>
    </div>

    <div class="row">
        <a href="{{ route('inventarios.index') }}" class="btn secondary">Volver al listado</a>
    </div>
</div>
@endsection
