@extends('layouts.app')

@section('title', $licencia->exists ? 'Editar licencia' : 'Nueva licencia')

@section('content')
<div class="workspace-view view-form">
<div class="panel page-header" style="max-width: 900px; margin: 0 auto;">
    <h1>{{ $licencia->exists ? 'Editar licencia' : 'Nueva licencia' }}</h1>
    @if($cupo)
        <p class="muted">Cupo anual {{ $cupo['anio'] }} del agente: {{ $cupo['cupo'] }} días corridos · utilizados {{ $cupo['utilizados'] }} · disponibles {{ $cupo['disponibles'] }}. Solo la licencia anual consume cupo.</p>
    @endif
    <form method="POST" action="{{ $licencia->exists ? route('licencias.update', $licencia) : route('licencias.store') }}" class="record-form">
        @csrf
        @if($licencia->exists)
            @method('PUT')
        @endif

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:2; min-width:240px;">
                <label for="policia_id">Agente</label>
                <select id="policia_id" name="policia_id" required>
                    <option value="">Seleccione</option>
                    @foreach($policias as $policia)
                        <option value="{{ $policia->id }}" {{ (string) old('policia_id', $licencia->policia_id) === (string) $policia->id ? 'selected' : '' }}>{{ $policia->persona?->nombre_completo }} ({{ $policia->numero_legajo }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="tipo_licencia_id">Tipo</label>
                <select id="tipo_licencia_id" name="tipo_licencia_id" required>
                    <option value="">Seleccione</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}" {{ (string) old('tipo_licencia_id', $licencia->tipo_licencia_id) === (string) $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}{{ $tipo->computa_cupo_anual ? ' (consume cupo)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:140px;">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    @foreach($estados as $valor => $texto)
                        @if($puedeAutorizar || $valor === ($licencia->estado ?? 'pendiente'))
                            <option value="{{ $valor }}" {{ old('estado', $licencia->estado) === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:140px;">
                <label for="anio_correspondiente">Año correspondiente</label>
                <input id="anio_correspondiente" name="anio_correspondiente" type="number" min="1980" max="{{ now()->year + 1 }}" value="{{ old('anio_correspondiente', $licencia->anio_correspondiente) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:160px;">
                <label for="fecha_inicio">Fecha de salida</label>
                <input id="fecha_inicio" name="fecha_inicio" type="date" value="{{ old('fecha_inicio', $licencia->fecha_inicio?->format('Y-m-d')) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:140px;">
                <label for="cantidad_dias">Días solicitados (corridos)</label>
                <input id="cantidad_dias" name="cantidad_dias" type="number" min="1" max="365" value="{{ old('cantidad_dias', $licencia->cantidad_dias) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:160px;">
                <label for="fecha_regreso_efectivo">Regreso efectivo</label>
                <input id="fecha_regreso_efectivo" name="fecha_regreso_efectivo" type="date" value="{{ old('fecha_regreso_efectivo', $licencia->fecha_regreso_efectivo?->format('Y-m-d')) }}">
            </div>
        </div>
        @if($licencia->fecha_regreso_prevista)
            <p class="muted">Regreso previsto calculado: {{ $licencia->fecha_regreso_prevista->format('d/m/Y') }}.</p>
        @endif

        <div class="field">
            <label for="motivo">Motivo</label>
            <textarea id="motivo" name="motivo" rows="2" maxlength="2000">{{ old('motivo', $licencia->motivo) }}</textarea>
        </div>

        <fieldset class="field">
            <legend>Cobertura de funciones</legend>
            <input type="hidden" name="requiere_reemplazo" value="0">
            <label style="font-weight:normal;"><input type="checkbox" name="requiere_reemplazo" value="1" {{ old('requiere_reemplazo', $licencia->requiere_reemplazo) ? 'checked' : '' }}> Requiere designar un reemplazo</label>
            <div class="row" style="gap:16px;">
                <div class="field" style="flex:2; min-width:240px;">
                    <label for="reemplazo_id">Quién cubre las funciones</label>
                    <select id="reemplazo_id" name="reemplazo_id">
                        <option value="">Sin reemplazo</option>
                        @foreach($policias as $policia)
                            <option value="{{ $policia->id }}" {{ (string) old('reemplazo_id', $licencia->reemplazo_id) === (string) $policia->id ? 'selected' : '' }}>{{ $policia->persona?->nombre_completo }} ({{ $policia->numero_legajo }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="flex:1; min-width:150px;">
                    <label for="cobertura_desde">Desde</label>
                    <input id="cobertura_desde" name="cobertura_desde" type="date" value="{{ old('cobertura_desde', $licencia->cobertura_desde?->format('Y-m-d')) }}">
                </div>
                <div class="field" style="flex:1; min-width:150px;">
                    <label for="cobertura_hasta">Hasta</label>
                    <input id="cobertura_hasta" name="cobertura_hasta" type="date" value="{{ old('cobertura_hasta', $licencia->cobertura_hasta?->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="field">
                <label for="cobertura_observaciones">Observaciones de la cobertura</label>
                <textarea id="cobertura_observaciones" name="cobertura_observaciones" rows="2" maxlength="2000">{{ old('cobertura_observaciones', $licencia->cobertura_observaciones) }}</textarea>
            </div>
        </fieldset>

        <div class="field">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="2" maxlength="5000">{{ old('observaciones', $licencia->observaciones) }}</textarea>
        </div>

        <div class="row form-actions">
            <button type="submit" class="action-button action-primary">Guardar</button>
            <a href="{{ $licencia->exists ? route('licencias.show', $licencia) : route('licencias.index') }}" class="action-button action-neutral">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
