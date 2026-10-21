@extends('layouts.app')
@section('title', $recurso->exists ? 'Editar recurso' : 'Registrar recurso')
@section('content')
<nav class="breadcrumbs" aria-label="Ubicación"><span>Movilidad</span><span aria-hidden="true">/</span><a href="{{ route('movilidad.'.$modulo.'.index') }}">{{ $titulo }}</a><span aria-hidden="true">/</span><span aria-current="page">{{ $recurso->exists ? 'Editar' : 'Nuevo registro' }}</span></nav>
<x-movilidad.page-header :title="($recurso->exists ? 'Editar' : 'Registrar').' '.($modulo === 'drones' ? 'dron / robot' : 'batería')" description="Completá los datos del recurso. Los campos marcados con * son obligatorios." />
@php
    $identidad = $modulo === 'drones' ? ['tipo','nombre','codigo','marca','modelo','numero_serie'] : ['codigo','marca','modelo','numero_serie','capacidad'];
    $operacion = $modulo === 'drones' ? ['estado','responsable_id','fecha_adquisicion','horas_uso'] : ['dron_robot_id','estado','ciclos','fecha_adquisicion','ultima_revision'];
@endphp
<form class="card form-card" method="POST" action="{{ $recurso->exists ? route('movilidad.'.$modulo.'.update', $recurso) : route('movilidad.'.$modulo.'.store') }}">
    @csrf @if($recurso->exists) @method('PUT') @endif
    <section class="form-section"><div class="section-heading"><h2>Identificación del recurso</h2><p>Datos de registro y características del equipo.</p></div><div class="grid">@foreach($identidad as $campo)@include('movilidad.partials.resource-field', ['label' => $campos[$campo]])@endforeach</div></section>
    <section class="form-section"><div class="section-heading"><h2>Asignación y seguimiento</h2><p>Estado actual, adquisición y {{ $modulo === 'drones' ? 'horas acumuladas de operación' : 'ciclos y revisiones' }}.</p></div><div class="grid">@foreach($operacion as $campo)@include('movilidad.partials.resource-field', ['label' => $campos[$campo]])@endforeach</div></section>
    <section class="form-section"><div class="section-heading"><h2>Observaciones</h2><p>Información complementaria para el seguimiento del recurso.</p></div>@include('movilidad.partials.resource-field', ['campo' => 'observaciones', 'label' => $campos['observaciones']])</section>
    <div class="form-footer"><p>{{ $modulo === 'drones' ? 'Las horas de uso son acumulativas y no pueden disminuir.' : 'Los ciclos son acumulativos. Indicá la capacidad con su unidad, por ejemplo 5000 mAh.' }}</p><div class="actions"><a class="button secondary" href="{{ route('movilidad.'.$modulo.'.index') }}">Cancelar</a><button type="submit">Guardar {{ $recurso->exists ? 'cambios' : 'registro' }}</button></div></div>
</form>
@endsection
