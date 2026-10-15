@extends('layouts.app')

@section('title', isset($oficina->id) ? 'Editar oficina' : 'Nueva oficina')

@section('content')
<div class="panel" style="max-width: 760px; margin: 0 auto;">
    <h1>{{ isset($oficina->id) ? 'Editar oficina' : 'Nueva oficina' }}</h1>
    <form method="POST" action="{{ isset($oficina->id) ? route('oficinas.update', $oficina) : route('oficinas.store') }}">
        @csrf
        @if(isset($oficina->id))
            @method('PUT')
        @endif
        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:240px;">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" value="{{ old('nombre', $oficina->nombre) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:240px;">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ old('estado', $oficina->estado) === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $oficina->descripcion) }}</textarea>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:240px;">
                <label for="ubicacion">Ubicación</label>
                <input id="ubicacion" name="ubicacion" value="{{ old('ubicacion', $oficina->ubicacion) }}">
            </div>
            <div class="field" style="flex:1; min-width:240px;">
                <label for="telefono">Teléfono</label>
                <input id="telefono" name="telefono" value="{{ old('telefono', $oficina->telefono) }}">
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:240px;">
                <label for="correo_electronico">Correo electrónico</label>
                <input id="correo_electronico" name="correo_electronico" type="email" value="{{ old('correo_electronico', $oficina->correo_electronico) }}">
            </div>
            <div class="field" style="flex:1; min-width:240px;">
                <label for="responsable_id">Responsable</label>
                <select id="responsable_id" name="responsable_id">
                    <option value="">Sin responsable</option>
                    @foreach($responsables as $responsable)
                        <option value="{{ $responsable->id }}" {{ old('responsable_id', $oficina->responsable_id) == $responsable->id ? 'selected' : '' }}>
                            {{ $responsable->persona?->nombre_completo ?? 'Policía sin nombre' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $oficina->observaciones) }}</textarea>
        </div>

        <div class="row">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ route('oficinas.index') }}" class="btn secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
