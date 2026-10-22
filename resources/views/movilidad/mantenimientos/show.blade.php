@extends('layouts.app')
@section('title', 'Detalle de mantenimiento')
@section('content')
<nav class="breadcrumbs" aria-label="Ubicación"><span>Movilidad</span><span aria-hidden="true">/</span><a href="{{ route($prefijo.'index', $recurso) }}">{{ $recurso->codigo }} · Mantenimientos</a><span aria-hidden="true">/</span><span aria-current="page">Registro #{{ $mantenimiento->id }}</span></nav>
<x-movilidad.page-header :title="'Mantenimiento #'.$mantenimiento->id" :description="'Intervención sobre el recurso '.$recurso->codigo.'.'">
    <a class="button secondary" href="{{ route($prefijo.'index', $recurso) }}">Volver al historial</a>@can('movilidad.mantenimiento.update', $mantenimiento)<a class="button" href="{{ route($prefijo.'edit', [$recurso,$mantenimiento]) }}">Editar mantenimiento</a>@endcan
</x-movilidad.page-header>
<section class="card"><div class="card-heading"><h2>Datos de la intervención</h2><x-movilidad.state :value="$mantenimiento->estado" /></div><dl class="detail-grid">
    <div><dt>Recurso</dt><dd>{{ $recurso->codigo }}</dd></div><div><dt>Tipo de mantenimiento</dt><dd>{{ $mantenimiento->tipo === 'revision' ? 'Revisión' : ucfirst($mantenimiento->tipo) }}</dd></div><div><dt>Fecha</dt><dd>{{ $mantenimiento->fecha->format('d/m/Y') }}</dd></div>
    <div><dt>Responsable</dt><dd>{{ $mantenimiento->responsable?->nombre_completo ?: 'Sin responsable' }}</dd></div><div><dt>Costo</dt><dd>{{ $mantenimiento->costo !== null ? number_format((float)$mantenimiento->costo, 2, ',', '.') : 'Sin registrar' }}</dd></div>
    @foreach(['descripcion'=>'Descripción del trabajo','resultado'=>'Resultado','observaciones'=>'Observaciones'] as $campo=>$label)<div class="full-width"><dt>{{ $label }}</dt><dd>{{ $mantenimiento->$campo ?: 'Sin registrar' }}</dd></div>@endforeach
</dl>@if(in_array($mantenimiento->estado, ['realizado','cancelado']))<div class="detail-note"><p>Esta intervención forma parte del historial del recurso y se conserva sin edición ni eliminación.</p></div>@endif</section>
@endsection
