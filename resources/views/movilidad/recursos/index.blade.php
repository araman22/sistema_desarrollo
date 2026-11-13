@extends('layouts.app')
@section('title', $titulo)
@section('content')
<div class="workspace-view view-index">
<div class="actions page-header"><div><h1>{{ $titulo }}</h1><p class="muted">Recursos, estado operativo y seguimiento de uso.</p></div>@can('create', $modelo)<a class="action-button action-primary" href="{{ route('movilidad.'.$modulo.'.create') }}">{{ $modulo === 'drones' ? 'Nuevo dron / robot' : 'Nueva batería' }}</a>@endcan</div>
<form class="card filters panel filter-panel" method="GET"><x-field name="q" label="Buscar código o serie" :value="request('q')" maxlength="150" />
<div><div class="filter-field"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option>@foreach($modelo::ESTADOS as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ str_replace('_',' ',ucfirst($estado)) }}</option>@endforeach</select></div></div>
@if($modulo === 'drones')<div><div class="filter-field"><label for="tipo">Tipo</label><select id="tipo" name="tipo"><option value="">Todos</option>@foreach($modelo::TIPOS as $tipo)<option @selected(request('tipo') === $tipo)>{{ $tipo }}</option>@endforeach</select></div></div>@endif
<button class="action-button action-primary">Buscar</button><a href="{{ route('movilidad.'.$modulo.'.index') }}" class="action-button action-neutral">Limpiar</a></form>
<section class="card table-wrap"><div class="table-panel"><table><thead><tr><th>Código / serie</th><th>{{ $modulo === 'drones' ? 'Nombre / tipo' : 'Dron / robot' }}</th><th>Estado</th><th>{{ $modulo === 'drones' ? 'Horas' : 'Ciclos' }}</th><th class="actions-column">Acciones</th></tr></thead><tbody>
@forelse($recursos as $recurso)<tr><td><strong>{{ $recurso->codigo }}</strong><br><small>{{ $recurso->numero_serie ?? 'Sin serie' }}</small></td><td>@if($modulo === 'drones'){{ $recurso->nombre }}<br><small>{{ $recurso->tipo }}</small>@else{{ $recurso->droneRobot?->codigo ?? 'Sin recurso' }}@endif</td><td><span class="badge">{{ str_replace('_',' ',$recurso->estado) }}</span></td><td>{{ $modulo === 'drones' ? $recurso->horas_uso : $recurso->ciclos }}</td><td><div class="actions record-actions"><a href="{{ route('movilidad.'.$modulo.'.show', $recurso) }}" class="action-button action-view">Detalle</a>@can('update', $recurso)<a href="{{ route('movilidad.'.$modulo.'.edit', $recurso) }}" class="action-button action-edit">Editar</a>@endcan @can('delete', $recurso)<x-delete-button :action="route('movilidad.'.$modulo.'.destroy', $recurso)" />@endcan</div></td></tr>
@empty<tr><td colspan="5" class="empty">No hay recursos para estos filtros.</td></tr>@endforelse
</tbody></table></div>{{ $recursos->links() }}</section>
</div>
@endsection
