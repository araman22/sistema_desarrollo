@extends('layouts.app')

@section('title', 'Personal')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Personal</h1>
        <p class="muted">Agentes de la dependencia, su situación de revista y antigüedad.</p>
    </div>
    @can('create', App\Models\Policia::class)
        <a href="{{ route('personal.create') }}" class="btn">Nuevo agente</a>
    @endcan
</div>

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Apellido, nombre, DNI o legajo">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="jerarquia_id">Jerarquía</label>
            <select id="jerarquia_id" name="jerarquia_id">
                <option value="">Todas</option>
                @foreach($jerarquias as $jerarquia)
                    <option value="{{ $jerarquia->id }}" {{ (string) request('jerarquia_id') === (string) $jerarquia->id ? 'selected' : '' }}>{{ $jerarquia->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="oficina_id">Oficina</label>
            <select id="oficina_id" name="oficina_id">
                <option value="">Todas</option>
                @foreach($oficinas as $oficina)
                    <option value="{{ $oficina->id }}" {{ (string) request('oficina_id') === (string) $oficina->id ? 'selected' : '' }}>{{ $oficina->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="situacion">Situación de revista</label>
            <select id="situacion" name="situacion">
                <option value="">Todas</option>
                @foreach($situaciones as $valor => $texto)
                    <option value="{{ $valor }}" {{ request('situacion') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
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
                <th>Legajo</th>
                <th>Agente</th>
                <th>Jerarquía</th>
                <th>Función principal</th>
                <th>Oficina</th>
                <th>Antigüedad</th>
                <th>Situación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($policias as $policia)
                <tr>
                    <td>{{ $policia->numero_legajo ?? '—' }}</td>
                    <td>{{ $policia->persona?->nombre_completo }}<br><span class="muted">DNI {{ $policia->persona?->dni }}</span></td>
                    <td>{{ $policia->jerarquia?->nombre ?? '—' }}</td>
                    <td>{{ $policia->funcion?->nombre ?? '—' }}</td>
                    <td>{{ $policia->oficina?->nombre ?? 'Sin oficina' }}</td>
                    <td>{{ $policia->antiguedad_texto }}</td>
                    <td><span class="badge">{{ $situaciones[$policia->situacionActual()] }}</span>@if($policia->estado !== 'activo') <span class="badge">{{ ucfirst($policia->estado) }}</span>@endif</td>
                    <td class="row">
                        <a href="{{ route('personal.show', $policia) }}" class="action-button action-view">Ver</a>
                        @can('update', $policia)
                            <a href="{{ route('personal.edit', $policia) }}" class="action-button action-edit">Editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No hay agentes que coincidan con los filtros.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $policias])
</div>
@endsection
