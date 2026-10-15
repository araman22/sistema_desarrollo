@extends('layouts.app')

@section('title', 'Detalle de equipo')

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $equipamientoTecnologico->nombre }}</h1>
            <p class="muted">{{ $equipamientoTecnologico->tipo }} · {{ $equipamientoTecnologico->codigo }}</p>
        </div>
        @can('update', $equipamientoTecnologico)
            <a href="{{ route('equipamientos-tecnologicos.edit', $equipamientoTecnologico) }}" class="btn">Editar</a>
        @endcan
    </div>

    <div class="grid">
        <div class="card"><strong>Marca:</strong> {{ $equipamientoTecnologico->marca ?? '—' }}</div>
        <div class="card"><strong>Modelo:</strong> {{ $equipamientoTecnologico->modelo ?? '—' }}</div>
        <div class="card"><strong>Serie:</strong> {{ $equipamientoTecnologico->numero_serie ?? '—' }}</div>
        <div class="card"><strong>Estado:</strong> {{ $equipamientoTecnologico->estado }}</div>
        <div class="card"><strong>Oficina:</strong> {{ $equipamientoTecnologico->oficina?->nombre ?? 'Sin asignación' }}</div>
        <div class="card"><strong>IP:</strong> {{ $equipamientoTecnologico->direccion_ip ?? '—' }}</div>
        <div class="card"><strong>MAC:</strong> {{ $equipamientoTecnologico->direccion_mac ?? '—' }}</div>
        <div class="card"><strong>SO:</strong> {{ $equipamientoTecnologico->sistema_operativo ?? '—' }}</div>
    </div>

    <div class="field" style="margin-top:20px;">
        <label>Observaciones</label>
        <p>{{ $equipamientoTecnologico->observaciones ?? 'Sin observaciones' }}</p>
    </div>

    <div class="row">
        <a href="{{ route('equipamientos-tecnologicos.index') }}" class="btn secondary">Volver al listado</a>
    </div>
</div>
@endsection
