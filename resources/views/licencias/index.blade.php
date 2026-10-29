@extends('layouts.app')

@section('title', 'Licencias')

@section('content')
<div class="workspace-view view-index">
<div class="row page-header" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Licencias</h1>
        <p class="muted">Licencias del personal, cupo anual y cobertura de funciones.</p>
    </div>
    @can('create', App\Models\Licencia::class)
        <a href="{{ route('licencias.create') }}" class="action-button action-primary">Nueva licencia</a>
    @endcan
</div>

<form method="GET" class="panel filter-panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <div class="filter-field"><label for="policia_id">Agente</label>
            <select id="policia_id" name="policia_id">
                <option value="">Todos</option>
                @foreach($policias as $policia)
                    <option value="{{ $policia->id }}" {{ (string) request('policia_id') === (string) $policia->id ? 'selected' : '' }}>{{ $policia->persona?->nombre_completo }} ({{ $policia->numero_legajo }})</option>
                @endforeach
            </select></div>
        </div>
        <div style="flex:1; min-width:170px;">
            <div class="filter-field"><label for="tipo_licencia_id">Tipo</label>
            <select id="tipo_licencia_id" name="tipo_licencia_id">
                <option value="">Todos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ (string) request('tipo_licencia_id') === (string) $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                @endforeach
            </select></div>
        </div>
        <div style="flex:1; min-width:140px;">
            <div class="filter-field"><label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach($estados as $valor => $texto)
                    <option value="{{ $valor }}" {{ request('estado') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                @endforeach
            </select></div>
        </div>
        <div style="flex:1; min-width:110px;">
            <div class="filter-field"><label for="anio">Año</label>
            <input id="anio" name="anio" type="number" min="1980" value="{{ request('anio') }}"></div>
        </div>
        <div>
            <button type="submit" class="action-button action-primary">Filtrar</button>
            <a href="{{ url()->current() }}" class="action-button action-neutral">Limpiar</a>
        </div>
    </div>
</form>

<div class="panel" style="margin-top: 20px;">
    <div class="table-panel"><table>
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
                <th class="actions-column">Acciones</th>
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
    </table></div>

    @include('partials.pagination', ['paginator' => $licencias])
</div>
</div>
@endsection
