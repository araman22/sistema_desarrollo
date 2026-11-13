@extends('layouts.app')
@section('title', 'Detalle de mantenimiento')
@section('content')
<div class="workspace-view view-show">
<h1>Mantenimiento #{{ $mantenimiento->id }} · {{ $recurso->codigo }}</h1><section class="card"><dl class="grid">
@foreach(['tipo'=>'Tipo','estado'=>'Estado','descripcion'=>'Descripción','costo'=>'Costo','resultado'=>'Resultado','observaciones'=>'Observaciones'] as $campo=>$label)<div><dt>{{ $label }}</dt><dd>{{ $mantenimiento->$campo ?? '—' }}</dd></div>@endforeach
<div><dt>Fecha</dt><dd>{{ $mantenimiento->fecha->format('d/m/Y') }}</dd></div><div><dt>Responsable</dt><dd>{{ $mantenimiento->responsable?->nombre_completo ?: 'Sin responsable' }}</dd></div>
</dl><div class="actions record-actions"><a href="{{ route($prefijo.'index', $recurso) }}" class="action-button action-neutral">Volver al historial</a>@can('movilidad.mantenimiento.update', $mantenimiento)<a class="action-button action-edit" href="{{ route($prefijo.'edit', [$recurso,$mantenimiento]) }}">Editar</a>@endcan</div></section>
</div>
@endsection
