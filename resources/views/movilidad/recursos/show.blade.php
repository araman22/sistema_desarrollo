@extends('layouts.app')
@section('title', $recurso->codigo)
@section('content')
<div class="workspace-view view-show">
<h1>{{ $recurso->codigo }} · {{ $titulo }}</h1><section class="card"><dl class="grid">
@foreach($campos as $campo=>$label)<div><dt>{{ $label }}</dt><dd>@if($campo === 'responsable_id'){{ $recurso->responsable?->nombre_completo ?: 'Sin responsable' }}@elseif($campo === 'dron_robot_id'){{ $recurso->droneRobot?->codigo ?? 'Sin recurso' }}@elseif(in_array($campo,['fecha_adquisicion','ultima_revision'])){{ $recurso->$campo?->format('d/m/Y') ?? '—' }}@else{{ $recurso->$campo ?? '—' }}@endif</dd></div>@endforeach
</dl><div class="actions record-actions">@can('update', $recurso)<a class="action-button action-edit" href="{{ route('movilidad.'.$modulo.'.edit', $recurso) }}">Editar</a>@endcan<a href="{{ route('movilidad.'.$modulo.'.index') }}" class="action-button action-neutral">Volver al listado</a></div><p class="muted">Para retirar un recurso con historial, cambiá su estado a baja.</p></section>
@if($baterias !== null)<section class="card"><h2>Baterías asociadas</h2>@forelse($baterias as $bateria)<p><a href="{{ route('movilidad.baterias.show', $bateria) }}">{{ $bateria->codigo }}</a> · {{ $bateria->estado }} · {{ $bateria->ciclos }} ciclos</p>@empty<p class="muted">Sin baterías asociadas.</p>@endforelse</section>@endif
@if($historial !== null)<section class="card"><div class="actions"><h2>Revisiones y mantenimientos</h2><a href="{{ route('movilidad.'.$modulo.'.mantenimientos.index', $recurso) }}" class="action-button action-view">Ver historial completo</a>@can('movilidad.mantenimiento.create', $recurso)<a class="action-button action-primary" href="{{ route('movilidad.'.$modulo.'.mantenimientos.create', $recurso) }}">Registrar mantenimiento</a>@endcan</div>
@include('movilidad.mantenimientos.table', ['mantenimientos' => $historial, 'prefijo' => 'movilidad.'.$modulo.'.mantenimientos.']){{ $historial->links() }}</section>@endif
</div>
@endsection
