@extends('layouts.app')

@section('title', 'Detalle de licencia')

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $licencia->tipo?->nombre ?? 'Licencia' }} · {{ $licencia->anio_correspondiente }}</h1>
            <p class="muted"><a href="{{ route('personal.show', $licencia->policia) }}">{{ $licencia->policia?->persona?->nombre_completo }}</a> · <span class="badge">{{ $licencia->estado_texto }}</span></p>
        </div>
        <div class="row">
            @can('update', $licencia)
                <a href="{{ route('licencias.edit', $licencia) }}" class="action-button action-edit">Editar</a>
            @endcan
            @can('delete', $licencia)
                <form method="POST" action="{{ route('licencias.destroy', $licencia) }}" onsubmit="return confirm('¿Eliminar la licencia pendiente?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-button action-delete">Eliminar</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid">
        <div class="card"><strong>Fecha de salida:</strong> {{ $licencia->fecha_inicio->format('d/m/Y') }}</div>
        <div class="card"><strong>Días solicitados:</strong> {{ $licencia->cantidad_dias }} corridos</div>
        <div class="card"><strong>Último día:</strong> {{ $licencia->fecha_fin?->format('d/m/Y') }}</div>
        <div class="card"><strong>Regreso previsto:</strong> {{ $licencia->fecha_regreso_prevista?->format('d/m/Y') ?? '—' }}</div>
        <div class="card"><strong>Regreso efectivo:</strong> {{ $licencia->fecha_regreso_efectivo?->format('d/m/Y') ?? 'Pendiente' }}</div>
        @if($cupo)
            <div class="card"><strong>Cupo {{ $cupo['anio'] }}:</strong> {{ $cupo['utilizados'] }} de {{ $cupo['cupo'] }} días utilizados · {{ $cupo['disponibles'] }} disponibles</div>
        @endif
    </div>

    <h2 style="margin-top:20px;">Cobertura de funciones</h2>
    @if($licencia->requiere_reemplazo)
        <div class="grid">
            <div class="card"><strong>Reemplazo:</strong> {{ $licencia->reemplazo?->persona?->nombre_completo ?? '—' }}</div>
            <div class="card"><strong>Período:</strong> {{ $licencia->cobertura_desde?->format('d/m/Y') }} al {{ $licencia->cobertura_hasta?->format('d/m/Y') }}</div>
        </div>
        <p>{{ $licencia->cobertura_observaciones }}</p>
    @else
        <p class="muted">No requiere designar reemplazo.</p>
    @endif

    <div class="field" style="margin-top:20px;">
        <label>Motivo</label>
        <p>{{ $licencia->motivo ?? '—' }}</p>
    </div>
    <div class="field">
        <label>Observaciones</label>
        <p>{{ $licencia->observaciones ?? '—' }}</p>
    </div>

    <div class="row">
        <a href="{{ route('licencias.index') }}" class="btn secondary">Volver al listado</a>
    </div>
</div>
@endsection
