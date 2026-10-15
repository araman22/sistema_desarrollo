@extends('layouts.app')

@section('title', 'Oficinas')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px; align-items:flex-end;">
    <div>
        <h1>Oficinas</h1>
        <p class="muted">Listado y administración de dependencias.</p>
    </div>
    @can('create', App\Models\Oficina::class)
        <a href="{{ route('oficinas.create') }}" class="btn">Nueva oficina</a>
    @endcan
</div>

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, ubicación o correo">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                <option value="activa" {{ request('estado') === 'activa' ? 'selected' : '' }}>Activa</option>
                <option value="inactiva" {{ request('estado') === 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                <option value="cerrada" {{ request('estado') === 'cerrada' ? 'selected' : '' }}>Cerrada</option>
            </select>
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="responsable_id">Responsable</label>
            <select id="responsable_id" name="responsable_id">
                <option value="">Todos</option>
                @foreach($responsables as $responsable)
                    <option value="{{ $responsable->id }}" {{ request('responsable_id') == $responsable->id ? 'selected' : '' }}>
                        {{ $responsable->persona?->nombre_completo ?? 'Sin nombre' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn">Filtrar</button>
        </div>
        <div>
            <a href="{{ route('oficinas.index') }}" class="btn secondary">Limpiar</a>
        </div>
    </div>
</form>

<div class="panel" style="margin-top: 20px;">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Ubicación</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Acciones</th>
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
                        <a href="{{ route('oficinas.show', $oficina) }}" class="btn secondary">Ver</a>
                        @can('update', $oficina)
                            <a href="{{ route('oficinas.edit', $oficina) }}" class="btn secondary">Editar</a>
                        @endcan
                        @can('delete', $oficina)
                            <form method="POST" action="{{ route('oficinas.destroy', $oficina) }}" onsubmit="return confirm('¿Desea eliminar la oficina?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn danger">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No hay oficinas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $oficinas])
</div>
@endsection
