@extends('layouts.app')

@section('title', 'Licencias')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Licencias</h1>
        <p class="muted">Licencias del personal, cupo anual y cobertura de funciones.</p>
    </div>
    @can('create', App\Models\Licencia::class)
        <a href="{{ route('licencias.create') }}" class="btn">Nueva licencia</a>
    @endcan
</div>

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <label for="policia_id">Agente</label>
            <select id="policia_id" name="policia_id">
                <option value="">Todos</option>
                @foreach($policias as $policia)
                    <option value="{{ $policia->id }}" {{ (string) request('policia_id') === (string) $policia->id ? 'selected' : '' }}>{{ $policia->persona?->nombre_completo }} ({{ $policia->numero_legajo }})</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="tipo_licencia_id">Tipo</label>
            <select id="tipo_licencia_id" name="tipo_licencia_id">
                <option value="">Todos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ (string) request('tipo_licencia_id') === (string) $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:140px;">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach($estados as $valor => $texto)
                    <option value="{{ $valor }}" {{ request('estado') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:110px;">
            <label for="anio">Año</label>
            <input id="anio" name="anio" type="number" min="1980" value="{{ request('anio') }}">
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
                <th>Agente</th>
                <th>Tipo</th>
                <th>Año</th>
                <th>Salida</th>
                <th>Días</th>
                <th>Regreso previsto</th>
                <th>Regreso efectivo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($licencias as $licencia)
                <tr>
                    <td>{{ $licencia->policia?->persona?->nombre_completo }}</td>
                    <td>{{ $licencia->tipo?->nombre ?? '—' }}</td>
                    <td>{{ $licencia->anio_correspondiente }}</td>
                    <td>{{ $licencia->fecha_inicio->format('d/m/Y') }}</td>
                    <td>{{ $licencia->cantidad_dias }}</td>
                    <td>{{ $licencia->fecha_regreso_prevista?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $licencia->fecha_regreso_efectivo?->format('d/m/Y') ?? '—' }}</td>
                    <td><span class="badge">{{ $licencia->estado_texto }}</span></td>
                    <td class="row">
                        <a href="{{ route('licencias.show', $licencia) }}" class="action-button action-view">Ver</a>
                        @can('update', $licencia)
                            <a href="{{ route('licencias.edit', $licencia) }}" class="action-button action-edit">Editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No hay licencias que coincidan con los filtros.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $licencias])
</div>
@endsection
