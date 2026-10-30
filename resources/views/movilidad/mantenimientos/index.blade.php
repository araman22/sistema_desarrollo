@extends('layouts.app')
@section('title', 'Historial de '.$recurso->codigo)
@section('content')
<div class="workspace-view view-index">
<div class="actions page-header"><div><h1>Historial · {{ $recurso->codigo }}</h1><p class="muted">Revisiones, tareas programadas y trabajos realizados.</p></div>@can('movilidad.mantenimiento.create', $recurso)<a class="action-button action-primary" href="{{ route($prefijo.'create', $recurso) }}">Nuevo mantenimiento</a>@endcan</div>
<section class="card">@include('movilidad.mantenimientos.table'){{ $mantenimientos->links() }}</section>
<a href="{{ route($recurso instanceof App\Models\DroneRobot ? 'movilidad.drones.show' : 'movilidad.baterias.show', $recurso) }}" class="action-button action-neutral">Volver al recurso</a>
</div>
@endsection
