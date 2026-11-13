@extends('layouts.app')

@section('title', 'Equipamiento tecnológico')

@section('content')
<div class="workspace-view view-index">
<div class="row page-header" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Equipamiento tecnológico</h1>
        <p class="muted">Recursos informáticos y tecnológicos de la institución.</p>
    </div>
    @can('create', App\Models\EquipamientoTecnologico::class)
        <a href="{{ route('equipamientos-tecnologicos.create') }}" class="action-button action-primary">Nuevo equipo</a>
    @endcan
</div>

<form method="GET" class="panel filter-panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <div class="filter-field"><label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Código, nombre, tipo o serie"></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="tipo">Tipo</label>
            <input id="tipo" name="tipo" value="{{ request('tipo') }}"></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach(['activo','inactivo','en_mantenimiento','baja'] as $estado)
                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                @endforeach
            </select></div>
        </div>
        <div>
            <button type="submit" class="action-button action-primary">Filtrar</button>
            <a href="{{ url()->current() }}" class="action-button action-neutral">Limpiar</a>
        </div>
    </div>
</form>

<div class="panel" style="margin-top:20px;">
    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Oficina</th>
                <th>Estado</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipamientos as $equipamiento)
                <tr>
                    <td>{{ $equipamiento->codigo }}</td>
                    <td>{{ $equipamiento->nombre }}</td>
                    <td>{{ $equipamiento->tipo }}</td>
                    <td>{{ $equipamiento->oficina?->nombre ?? 'Sin oficina' }}</td>
                    <td><span class="badge">{{ $equipamiento->estado }}</span></td>
                    <td class="row">
                        <a href="{{ route('equipamientos-tecnologicos.show', $equipamiento) }}" class="action-button action-view">Ver</a>
                        @can('update', $equipamiento)
                            <a href="{{ route('equipamientos-tecnologicos.edit', $equipamiento) }}" class="action-button action-edit">Editar</a>
                        @endcan
                        @can('delete', $equipamiento)
                            <form method="POST" action="{{ route('equipamientos-tecnologicos.destroy', $equipamiento) }}" onsubmit="return confirm('¿Desea eliminar el equipo?');" class="record-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-button action-delete">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No hay equipos tecnológicos registrados.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @include('partials.pagination', ['paginator' => $equipamientos])
</div>
</div>
@endsection
