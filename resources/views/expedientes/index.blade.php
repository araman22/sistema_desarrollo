@extends('layouts.app')

@section('title', 'Expedientes')

@section('content')
<div class="row" style="justify-content: space-between; margin-bottom: 20px;">
    <div>
        <h1>Expedientes</h1>
        <p class="muted">Notas, memorándums y trámites organizados por bibliorato, con su recorrido de pases.</p>
    </div>
    <div class="row">
        <a href="{{ route('biblioratos.index') }}" class="btn secondary">Biblioratos</a>
        @can('create', App\Models\Expediente::class)
            <a href="{{ route('expedientes.create') }}" class="btn">Nuevo expediente</a>
        @endcan
    </div>
</div>

@if($pendientes > 0)
    <p class="panel" role="status">Hay <strong>{{ $pendientes }}</strong> expediente(s) pendiente(s) de recepción. <a href="{{ route('expedientes.index', ['estado' => 'pendiente_recepcion']) }}">Ver pendientes</a></p>
@endif

<form method="GET" class="panel">
    <div class="row" style="gap: 12px; align-items:end;">
        <div style="flex:2; min-width:200px;">
            <label for="search">Buscar</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Número, asunto, origen o destino">
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="bibliorato_id">Bibliorato</label>
            <select id="bibliorato_id" name="bibliorato_id">
                <option value="">Todos</option>
                @foreach($biblioratos as $bibliorato)
                    <option value="{{ $bibliorato->id }}" {{ (string) request('bibliorato_id') === (string) $bibliorato->id ? 'selected' : '' }}>{{ $bibliorato->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                @foreach($estados as $valor => $texto)
                    <option value="{{ $valor }}" {{ request('estado') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1; min-width:170px;">
            <label for="ubicacion">Ubicación actual</label>
            <input id="ubicacion" name="ubicacion" value="{{ request('ubicacion') }}">
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
                <th>Número</th>
                <th>Asunto</th>
                <th>Bibliorato</th>
                <th>Origen → Destino</th>
                <th>Emisión</th>
                <th>Ubicación actual</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expedientes as $expediente)
                <tr>
                    <td>{{ $expediente->numero }}</td>
                    <td>{{ $expediente->asunto }}</td>
                    <td>{{ $expediente->bibliorato?->nombre }}</td>
                    <td>{{ $expediente->origen }} → {{ $expediente->destino }}</td>
                    <td>{{ $expediente->fecha_emision->format('d/m/Y') }}</td>
                    <td>{{ $expediente->ubicacion_actual }}@if($expediente->paseEnCurso)<br><span class="muted">En camino a {{ $expediente->paseEnCurso->destino }}</span>@endif</td>
                    <td>{{ $expediente->responsable?->persona?->nombre_completo ?? '—' }}</td>
                    <td><span class="badge">{{ $expediente->estado_texto }}</span></td>
                    <td class="row">
                        <a href="{{ route('expedientes.show', $expediente) }}" class="action-button action-view">Ver</a>
                        @can('update', $expediente)
                            <a href="{{ route('expedientes.edit', $expediente) }}" class="action-button action-edit">Editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No hay expedientes que coincidan con los filtros.</td></tr>
            @endforelse
        </tbody>
    </table>

    @include('partials.pagination', ['paginator' => $expedientes])
</div>
@endsection
