@extends('layouts.app')
@section('title', 'Historial de '.$recurso->codigo)
@section('content')
@php($recursoRoute = $recurso instanceof App\Models\DroneRobot ? 'movilidad.drones.show' : 'movilidad.baterias.show')
<nav class="breadcrumbs" aria-label="Ubicación"><span>Movilidad</span><span aria-hidden="true">/</span><a href="{{ route($recursoRoute, $recurso) }}">{{ $recurso->codigo }}</a><span aria-hidden="true">/</span><span aria-current="page">Mantenimientos</span></nav>
<x-movilidad.page-header :title="'Mantenimientos · '.$recurso->codigo" description="Revisiones, tareas programadas y seguimiento de intervenciones.">
    <a class="button secondary" href="{{ route($recursoRoute, $recurso) }}">Volver al recurso</a>@can('movilidad.mantenimiento.create', $recurso)<a class="button" href="{{ route($prefijo.'create', $recurso) }}"><span aria-hidden="true">+</span>Nuevo mantenimiento</a>@endcan
</x-movilidad.page-header>
<section class="card"><div class="card-heading"><h2>Historial de intervenciones</h2><span class="record-count">{{ $mantenimientos->total() }} {{ $mantenimientos->total() === 1 ? 'registro' : 'registros' }}</span></div>@include('movilidad.mantenimientos.table')
<div class="table-footer"><span>@if($mantenimientos->total())Mostrando {{ $mantenimientos->firstItem() }}–{{ $mantenimientos->lastItem() }} de {{ $mantenimientos->total() }} registros @else Sin registros para mostrar @endif</span>@if($mantenimientos->hasPages()){{ $mantenimientos->links() }}@endif</div></section>
@endsection
