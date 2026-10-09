@extends('layouts.app')

@section('title', 'Equipamiento tecnológico')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Equipamiento tecnológico</h1>
        <p class="muted">Recursos informáticos y tecnológicos de la institución.</p>
    </div>
    @can('create', App\Models\EquipamientoTecnologico::class)
        <a href="{{ route('equipamientos-tecnologicos.create') }}" class="btn">Nuevo equipo</a>
    @endcan
</div>

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Código, nombre, tipo o serie">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="tipo">Tipo</label>
            <input id="tipo" name="tipo" value="{{ request('tipo') }}">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach(['activo','inactivo','en_mantenimiento','baja'] as $estado)
                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn">Filtrar</button>
        </div>
    </div>
</form>

<div class="panel" style="margin-top:20px;">
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Oficina</th>
                <th>Estado</th>
                <th>Acciones</th>
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
                        <a href="{{ route('equipamientos-tecnologicos.show', $equipamiento) }}" class="btn secondary">Ver</a>
                        @can('update', $equipamiento)
                            <a href="{{ route('equipamientos-tecnologicos.edit', $equipamiento) }}" class="btn secondary">Editar</a>
                        @endcan
                        @can('delete', $equipamiento)
                            <form method="POST" action="{{ route('equipamientos-tecnologicos.destroy', $equipamiento) }}" onsubmit="return confirm('¿Desea eliminar el equipo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn danger">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No hay equipos tecnológicos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $equipamientos])
</div>
@endsection
