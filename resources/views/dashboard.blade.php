@extends('layouts.app')

@section('title', 'Dashboard - SIGDET')

@section('content')
<div class="panel">
    <h1>Dashboard SIGDET</h1>
    <p class="muted">Resumen general del módulo de gestión del departamento.</p>
</div>

<div class="grid" style="margin-top: 20px;">
    <div class="card">
        <div class="muted">Oficinas</div>
        <h2>{{ $stats['oficinas'] }}</h2>
    </div>
    <div class="card">
        <div class="muted">Inventarios</div>
        <h2>{{ $stats['inventarios'] }}</h2>
    </div>
    <div class="card">
        <div class="muted">Equipamiento</div>
        <h2>{{ $stats['equipos'] }}</h2>
    </div>
    <div class="card">
        <div class="muted">Tipos documentales</div>
        <h2>{{ $stats['tipos_documentos'] }}</h2>
    </div>
    <div class="card">
        <div class="muted">Documentos</div>
        <h2>{{ $stats['documentos'] }}</h2>
    </div>
</div>

<div class="panel" style="margin-top: 24px;">
    <h2>Diferencia de caja chica</h2>
    <div class="grid" style="margin-top: 12px;">
        <div class="card">
            <div class="muted">Último ingreso</div>
            <h2>${{ number_format($stats['ultimo_ingreso'], 2, '.', '') }}</h2>
            <div class="small">Origen: {{ $stats['origen_ultimo_ingreso'] }}</div>
        </div>
        <div class="card">
            <div class="muted">Último egreso</div>
            <h2>${{ number_format($stats['ultimo_egreso'], 2, '.', '') }}</h2>
            <div class="small">Destino: {{ $stats['destino_ultimo_egreso'] }}</div>
        </div>
        <div class="card">
            <div class="muted">Diferencia actual</div>
            <h2>${{ number_format($stats['diferencia_caja_chica'], 2, '.', '') }}</h2>
            <div class="small">Ingreso menos egreso del último movimiento.</div>
        </div>
    </div>
</div>
@endsection
