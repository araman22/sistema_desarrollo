@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Inventario</h1>
        <p class="muted">Recursos y bienes asignados a oficinas y responsables.</p>
    </div>
    @can('create', App\Models\Inventario::class)
        <a href="{{ route('inventarios.create') }}" class="btn">Nuevo recurso</a>
    @endcan
</div>

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Código, descripción o serie">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="categoria">Categoría</label>
            <input id="categoria" name="categoria" value="{{ request('categoria') }}" placeholder="Ej. Tecnología">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach(['disponible','en_uso','en_mantenimiento','baja','perdido'] as $estado)
                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn">Filtrar</button>
        </div>
    </div>
</form>

<div class="panel" style="margin-top: 20px;">
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Oficina</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventarios as $inventario)
                <tr>
                    <td>{{ $inventario->codigo }}</td>
                    <td>{{ $inventario->descripcion }}</td>
                    <td>{{ $inventario->categoria }}</td>
                    <td>{{ $inventario->oficina?->nombre ?? 'Sin oficina' }}</td>
                    <td><span class="badge">{{ $inventario->estado }}</span></td>
                    <td class="row">
                        <a href="{{ route('inventarios.show', $inventario) }}" class="btn secondary">Ver</a>
                        @can('update', $inventario)
                            <a href="{{ route('inventarios.edit', $inventario) }}" class="btn secondary">Editar</a>
                        @endcan
                        @can('delete', $inventario)
                            <form method="POST" action="{{ route('inventarios.destroy', $inventario) }}" onsubmit="return confirm('¿Desea eliminar el recurso?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn danger">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No hay recursos de inventario registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $inventarios])
</div>
@endsection
