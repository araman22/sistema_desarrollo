@extends('layouts.app')
@section('title', $titulo)
@section('content')
<div class="actions"><div><h1>{{ $titulo }}</h1><p class="muted">Recursos, estado operativo y seguimiento de uso.</p></div>@can('create', $modelo)<a class="button" href="{{ route('movilidad.'.$modulo.'.create') }}">{{ $modulo === 'drones' ? 'Nuevo dron / robot' : 'Nueva batería' }}</a>@endcan</div>
<form class="card filters" method="GET"><x-field name="q" label="Buscar código o serie" :value="request('q')" maxlength="150" />
<div><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option>@foreach($modelo::ESTADOS as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ str_replace('_',' ',ucfirst($estado)) }}</option>@endforeach</select></div>
@if($modulo === 'drones')<div><label for="tipo">Tipo</label><select id="tipo" name="tipo"><option value="">Todos</option>@foreach($modelo::TIPOS as $tipo)<option @selected(request('tipo') === $tipo)>{{ $tipo }}</option>@endforeach</select></div>@endif
<button>Buscar</button><a href="{{ route('movilidad.'.$modulo.'.index') }}">Limpiar</a></form>
<section class="card table-wrap"><table><thead><tr><th>Código / serie</th><th>{{ $modulo === 'drones' ? 'Nombre / tipo' : 'Dron / robot' }}</th><th>Estado</th><th>{{ $modulo === 'drones' ? 'Horas' : 'Ciclos' }}</th><th>Acciones</th></tr></thead><tbody>
@forelse($recursos as $recurso)<tr><td><strong>{{ $recurso->codigo }}</strong><br><small>{{ $recurso->numero_serie ?? 'Sin serie' }}</small></td><td>@if($modulo === 'drones'){{ $recurso->nombre }}<br><small>{{ $recurso->tipo }}</small>@else{{ $recurso->droneRobot?->codigo ?? 'Sin recurso' }}@endif</td><td><span class="badge">{{ str_replace('_',' ',$recurso->estado) }}</span></td><td>{{ $modulo === 'drones' ? $recurso->horas_uso : $recurso->ciclos }}</td><td><div class="actions"><a href="{{ route('movilidad.'.$modulo.'.show', $recurso) }}">Detalle</a>@can('update', $recurso)<a href="{{ route('movilidad.'.$modulo.'.edit', $recurso) }}">Editar</a>@endcan @can('delete', $recurso)<x-delete-button :action="route('movilidad.'.$modulo.'.destroy', $recurso)" />@endcan</div></td></tr>
@empty<tr><td colspan="5" class="empty">No hay recursos para estos filtros.</td></tr>@endforelse
</tbody></table>{{ $recursos->links() }}</section>
@endsection
