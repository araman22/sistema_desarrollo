@extends('layouts.app')
@section('title', $recurso->codigo)
@section('content')
<nav class="breadcrumbs" aria-label="Ubicación"><span>Movilidad</span><span aria-hidden="true">/</span><a href="{{ route('movilidad.'.$modulo.'.index') }}">{{ $titulo }}</a><span aria-hidden="true">/</span><span aria-current="page">{{ $recurso->codigo }}</span></nav>
<x-movilidad.page-header :title="$recurso->codigo" :description="$modulo === 'drones' ? $recurso->nombre : 'Ficha de batería y seguimiento de uso.'">
    <a class="button secondary" href="{{ route('movilidad.'.$modulo.'.index') }}">Volver al listado</a>@can('update', $recurso)<a class="button" href="{{ route('movilidad.'.$modulo.'.edit', $recurso) }}">Editar recurso</a>@endcan
</x-movilidad.page-header>
<section class="card"><div class="card-heading"><h2>Información del recurso</h2><x-movilidad.state :value="$recurso->estado" /></div><dl class="detail-grid">
    @foreach($campos as $campo=>$label)
        @continue($campo === 'estado')
        <div @class(['full-width' => $campo === 'observaciones'])><dt>{{ $label }}</dt><dd>
        @if($campo === 'responsable_id')
            {{ $recurso->responsable?->nombre_completo ?: 'Sin responsable' }}
        @elseif($campo === 'dron_robot_id')
            {{ $recurso->droneRobot?->codigo ?? 'Sin recurso' }}
        @elseif(in_array($campo,['fecha_adquisicion','ultima_revision']))
            {{ $recurso->$campo?->format('d/m/Y') ?? 'Sin registrar' }}
        @elseif($campo === 'horas_uso')
            {{ number_format((float)$recurso->$campo, 2, ',', '.') }} h
        @elseif($campo === 'ciclos')
            {{ number_format($recurso->$campo, 0, ',', '.') }}
        @else
            {{ $recurso->$campo ?? 'Sin registrar' }}
        @endif
        </dd></div>
    @endforeach
    </dl><div class="detail-note"><p>Los recursos con historial se conservan. Para retirarlos de servicio, cambiá su estado a baja.</p></div>
</section>
@if($baterias !== null)
<section class="card"><div class="card-heading"><h2>Baterías asociadas</h2><span class="record-count">{{ $baterias->count() }} {{ $baterias->count() === 1 ? 'batería' : 'baterías' }}</span></div><div class="table-wrap"><table><thead><tr><th scope="col">Código</th><th scope="col">Estado</th><th scope="col" class="numeric">Ciclos acumulados</th><th scope="col" class="numeric">Acciones</th></tr></thead><tbody>
@forelse($baterias as $bateria)<tr><td><a class="record-code" href="{{ route('movilidad.baterias.show', $bateria) }}">{{ $bateria->codigo }}</a></td><td><x-movilidad.state :value="$bateria->estado" /></td><td class="numeric">{{ number_format($bateria->ciclos, 0, ',', '.') }}</td><td><div class="row-actions"><a href="{{ route('movilidad.baterias.show', $bateria) }}">Ver batería</a></div></td></tr>@empty<tr><td colspan="4" class="empty"><strong>Sin baterías asociadas</strong><p>Las baterías asignadas a este recurso aparecerán aquí.</p></td></tr>@endforelse
</tbody></table></div></section>
@endif
@if($historial !== null)
<section class="card"><div class="card-heading"><div><h2>Revisiones y mantenimientos</h2><p>Historial de intervenciones del recurso.</p></div><div class="actions"><a class="button secondary" href="{{ route('movilidad.'.$modulo.'.mantenimientos.index', $recurso) }}">Ver historial</a>@can('movilidad.mantenimiento.create', $recurso)<a class="button" href="{{ route('movilidad.'.$modulo.'.mantenimientos.create', $recurso) }}">Registrar mantenimiento</a>@endcan</div></div>
@include('movilidad.mantenimientos.table', ['mantenimientos' => $historial, 'prefijo' => 'movilidad.'.$modulo.'.mantenimientos.'])@if($historial->hasPages())<div class="table-footer">{{ $historial->links() }}</div>@endif</section>
@endif
@endsection
