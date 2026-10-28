@extends('layouts.app')

@section('title', 'Oficinas')

@section('content')
<div class="workspace-view view-index">
<div class="row page-header" style="justify-content: space-between; margin-bottom: 20px; align-items:flex-end;">
    <div>
        <h1>Oficinas</h1>
        <p class="muted">Administración de Dependencia.</p>
    </div>
    @can('create', App\Models\Oficina::class)
        <a href="{{ route('oficinas.create') }}" class="action-button action-primary">Nueva oficina</a>
    @endcan
</div>

<form method="GET" class="panel filter-panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <div class="filter-field"><label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, ubicación o correo"></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                <option value="activa" {{ request('estado') === 'activa' ? 'selected' : '' }}>Activa</option>
                <option value="inactiva" {{ request('estado') === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                <option value="cerrada" {{ request('estado') === 'cerrada' ? 'selected' : '' }}>Cerrada</option>
            </select></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="responsable_id">Responsable</label>
            <select id="responsable_id" name="responsable_id">
                <option value="">Todos</option>
                @foreach($responsables as $responsable)
                    <option value="{{ $responsable->id }}" {{ request('responsable_id') == $responsable->id ? 'selected' : '' }}>
                        {{ $responsable->persona?->nombre_completo ?? 'Sin nombre' }}
                    </option>
                @endforeach
            </select></div>
        </div>
        <div>
            <button type="submit" class="action-button action-primary">Filtrar</button>
        </div>
        <div>
            <a href="{{ route('oficinas.index') }}" class="action-button action-neutral">Limpiar</a>
        </div>
    </div>
</form>

<div class="panel" style="margin-top: 20px;">
    <div class="table-panel"><table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Ubicación</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th class="actions-column">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($oficinas as $oficina)
                <tr>
                    <td>{{ $oficina->nombre }}</td>
                    <td>{{ $oficina->ubicacion ?? '—' }}</td>
                    <td>{{ $oficina->responsable?->persona?->nombre_completo ?? 'No asignado' }}</td>
                    <td><span class="badge">{{ $oficina->estado }}</span></td>
                    <td class="row">
                        <a href="{{ route('oficinas.show', $oficina) }}" class="action-button action-view">Ver</a>
                        @can('update', $oficina)
                            <a href="{{ route('oficinas.edit', $oficina) }}" class="action-button action-edit">Editar</a>
                        @endcan
                        @can('delete', $oficina)
                            <form method="POST" action="{{ route('oficinas.destroy', $oficina) }}" onsubmit="return confirm('¿Desea eliminar la oficina?');" class="record-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-button action-delete">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No hay oficinas registradas.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @include('partials.pagination', ['paginator' => $oficinas])
</div>
</div>
@endsection
