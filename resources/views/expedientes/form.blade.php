@extends('layouts.app')

@section('title', $expediente->exists ? 'Editar expediente' : 'Nuevo expediente')

@section('content')
<div class="workspace-view view-form">
<div class="panel page-header" style="max-width: 900px; margin: 0 auto;">
    <h1>{{ $expediente->exists ? 'Editar expediente '.$expediente->numero : 'Nuevo expediente' }}</h1>
    <form method="POST" action="{{ $expediente->exists ? route('expedientes.update', $expediente) : route('expedientes.store') }}" class="record-form">
        @csrf
        @if($expediente->exists)
            @method('PUT')
        @endif

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:160px;">
                <label for="numero">Identificación / número</label>
                <input id="numero" name="numero" value="{{ old('numero', $expediente->numero) }}" maxlength="50" required>
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="bibliorato_id">Bibliorato / carpeta</label>
                <select id="bibliorato_id" name="bibliorato_id" required>
                    <option value="">Seleccione</option>
                    @foreach($biblioratos as $bibliorato)
                        <option value="{{ $bibliorato->id }}" {{ (string) old('bibliorato_id', $expediente->bibliorato_id) === (string) $bibliorato->id ? 'selected' : '' }}>{{ $bibliorato->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:160px;">
                <label for="estado">Estado del trámite</label>
                <select id="estado" name="estado" required>
                    @foreach($estados as $valor => $texto)
                        <option value="{{ $valor }}" {{ old('estado', $expediente->estado) === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="asunto">Asunto</label>
            <input id="asunto" name="asunto" value="{{ old('asunto', $expediente->asunto) }}" maxlength="255" required>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:200px;">
                <label for="origen">Origen</label>
                <input id="origen" name="origen" value="{{ old('origen', $expediente->origen) }}" maxlength="150" required>
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="destino">Destino</label>
                <input id="destino" name="destino" value="{{ old('destino', $expediente->destino) }}" maxlength="150" required>
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:160px;">
                <label for="fecha_emision">Fecha de emisión</label>
                <input id="fecha_emision" name="fecha_emision" type="date" value="{{ old('fecha_emision', $expediente->fecha_emision?->format('Y-m-d')) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:160px;">
                <label for="fecha_recepcion">Fecha de recepción</label>
                <input id="fecha_recepcion" name="fecha_recepcion" type="date" value="{{ old('fecha_recepcion', $expediente->fecha_recepcion?->format('Y-m-d')) }}">
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="ubicacion_actual">Ubicación actual</label>
                @if($expediente->exists)
                    <input id="ubicacion_actual" value="{{ $expediente->ubicacion_actual }}" disabled>
                    <small class="muted">Se actualiza al recibir un pase.</small>
                @else
                    <input id="ubicacion_actual" name="ubicacion_actual" value="{{ old('ubicacion_actual', $expediente->ubicacion_actual) }}" maxlength="150" required>
                @endif
            </div>
        </div>

        <div class="field">
            <label for="responsable_id">Responsable</label>
            <select id="responsable_id" name="responsable_id">
                <option value="">Sin asignar</option>
                @foreach($agentes as $agente)
                    <option value="{{ $agente->id }}" {{ (string) old('responsable_id', $expediente->responsable_id) === (string) $agente->id ? 'selected' : '' }}>{{ $agente->persona?->nombre_completo }} ({{ $agente->numero_legajo }})</option>
                @endforeach
            </select>
        </div>

        @php($asociados = array_map('strval', old('documentos', $expediente->exists ? $expediente->documentos->pluck('id')->all() : [])))
        <div class="field">
            <label for="documentos">Documentos asociados</label>
            <select id="documentos" name="documentos[]" multiple size="6">
                @foreach($documentos as $documento)
                    <option value="{{ $documento->id }}" {{ in_array((string) $documento->id, $asociados, true) ? 'selected' : '' }}>{{ $documento->tipo?->nombre }} N° {{ $documento->numero }} · {{ $documento->nombre }}</option>
                @endforeach
            </select>
            <small class="muted">Mantenga Ctrl presionado para elegir varios.</small>
        </div>

        <div class="field">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3" maxlength="5000">{{ old('observaciones', $expediente->observaciones) }}</textarea>
        </div>

        <div class="row form-actions">
            <button type="submit" class="action-button action-primary">Guardar</button>
            <a href="{{ $expediente->exists ? route('expedientes.show', $expediente) : route('expedientes.index') }}" class="action-button action-neutral">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
