@extends('layouts.app')
@section('title', $titulo)
@section('content')
<nav class="breadcrumbs" aria-label="Ubicación"><span>Movilidad</span><span aria-hidden="true">/</span><span aria-current="page">{{ $titulo }}</span></nav>
<x-movilidad.page-header :title="$titulo" :description="$modulo === 'drones' ? 'Administración de recursos, responsables y horas de operación.' : 'Control de baterías, asignaciones, ciclos y revisiones.'">
    @can('create', $modelo)<a class="button" href="{{ route('movilidad.'.$modulo.'.create') }}"><span aria-hidden="true">+</span>{{ $modulo === 'drones' ? 'Nuevo dron / robot' : 'Nueva batería' }}</a>@endcan
</x-movilidad.page-header>
<form class="card filters" method="GET" role="search" aria-label="Filtrar recursos">
    <x-field name="q" label="Buscar recurso" :value="request('q')" maxlength="150" placeholder="Código o número de serie" />
    <div><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos los estados</option>@foreach($modelo::ESTADOS as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>@endforeach</select></div>
    @if($modulo === 'drones')<div><label for="tipo">Tipo de recurso</label><select id="tipo" name="tipo"><option value="">Todos los tipos</option>@foreach($modelo::TIPOS as $tipo)<option value="{{ $tipo }}" @selected(request('tipo') === $tipo)>{{ $tipo === 'dron' ? 'Dron' : 'Robot' }}</option>@endforeach</select></div>@endif
    <button type="submit">Aplicar filtros</button><a class="button secondary" href="{{ route('movilidad.'.$modulo.'.index') }}">Limpiar</a>
</form>
<section class="card" aria-label="Listado de recursos">
    <div class="card-heading"><h2>Registro de {{ $modulo === 'drones' ? 'drones y robots' : 'baterías' }}</h2><span class="record-count">{{ $recursos->total() }} {{ $recursos->total() === 1 ? 'registro' : 'registros' }}</span></div>
    <div class="table-wrap"><table><thead><tr><th scope="col">Código / serie</th><th scope="col">{{ $modulo === 'drones' ? 'Recurso' : 'Asignación' }}</th><th scope="col">Estado</th><th scope="col" class="numeric">{{ $modulo === 'drones' ? 'Horas de uso' : 'Ciclos' }}</th><th scope="col" class="numeric">Acciones</th></tr></thead><tbody>
    @forelse($recursos as $recurso)
        <tr><td><a class="record-code" href="{{ route('movilidad.'.$modulo.'.show', $recurso) }}">{{ $recurso->codigo }}</a><span class="cell-secondary">{{ $recurso->numero_serie ?? 'Sin número de serie' }}</span></td>
        <td>@if($modulo === 'drones'){{ $recurso->nombre }}<span class="cell-secondary">{{ $recurso->tipo === 'dron' ? 'Dron' : 'Robot' }}</span>@else{{ $recurso->droneRobot?->codigo ?? 'Sin recurso' }}<span class="cell-secondary">{{ $recurso->droneRobot?->nombre }}</span>@endif</td>
        <td><x-movilidad.state :value="$recurso->estado" /></td><td class="numeric">{{ $modulo === 'drones' ? number_format((float)$recurso->horas_uso, 2, ',', '.') : number_format($recurso->ciclos, 0, ',', '.') }}</td>
        <td><div class="row-actions"><a href="{{ route('movilidad.'.$modulo.'.show', $recurso) }}">Detalle</a>@can('update', $recurso)<a href="{{ route('movilidad.'.$modulo.'.edit', $recurso) }}">Editar</a>@endcan @can('delete', $recurso)<x-delete-button :action="route('movilidad.'.$modulo.'.destroy', $recurso)" />@endcan</div></td></tr>
    @empty<tr><td colspan="5" class="empty"><strong>No se encontraron recursos</strong><p>{{ request()->filled('q') || request()->filled('estado') || request()->filled('tipo') ? 'Revisá los filtros aplicados para ampliar la búsqueda.' : 'Los recursos registrados aparecerán en este listado.' }}</p></td></tr>@endforelse
    </tbody></table></div>
    <div class="table-footer"><span>@if($recursos->total())Mostrando {{ $recursos->firstItem() }}–{{ $recursos->lastItem() }} de {{ $recursos->total() }} registros @else Sin registros para mostrar @endif</span>@if($recursos->hasPages()){{ $recursos->links() }}@endif</div>
</section>
@endsection
