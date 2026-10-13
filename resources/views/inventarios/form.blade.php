@extends('layouts.app')

@section('title', isset($inventario->id) ? 'Editar inventario' : 'Nuevo recurso')

@section('content')
<div class="panel" style="max-width: 820px; margin: 0 auto;">
    <h1>{{ isset($inventario->id) ? 'Editar recurso' : 'Nuevo recurso' }}</h1>
    <form method="POST" action="{{ isset($inventario->id) ? route('inventarios.update', $inventario) : route('inventarios.store') }}">
        @csrf
        @if(isset($inventario->id))
            @method('PUT')
        @endif

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="codigo">Código</label>
                <input id="codigo" name="codigo" value="{{ old('codigo', $inventario->codigo) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria" required>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria }}" {{ old('categoria', $inventario->categoria) === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ old('estado', $inventario->estado) === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" required>{{ old('descripcion', $inventario->descripcion) }}</textarea>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="marca">Marca</label>
                <input id="marca" name="marca" value="{{ old('marca', $inventario->marca) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="modelo">Modelo</label>
                <input id="modelo" name="modelo" value="{{ old('modelo', $inventario->modelo) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="numero_serie">Número de serie</label>
                <input id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $inventario->numero_serie) }}">
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="oficina_id">Oficina</label>
                <select id="oficina_id" name="oficina_id">
                    <option value="">Sin oficina</option>
                    @foreach($oficinas as $oficina)
                        <option value="{{ $oficina->id }}" {{ old('oficina_id', $inventario->oficina_id) == $oficina->id ? 'selected' : '' }}>{{ $oficina->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="responsable_id">Responsable</label>
                <select id="responsable_id" name="responsable_id">
                    <option value="">Sin responsable</option>
                    @foreach($responsables as $responsable)
                        <option value="{{ $responsable->id }}" {{ old('responsable_id', $inventario->responsable_id) == $responsable->id ? 'selected' : '' }}>
                            {{ $responsable->persona?->nombre_completo ?? 'Policía sin nombre' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="fecha_adquisicion">Fecha de adquisición</label>
                <input id="fecha_adquisicion" name="fecha_adquisicion" type="date" value="{{ old('fecha_adquisicion', optional($inventario->fecha_adquisicion)->format('Y-m-d')) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="valor_adquisicion">Valor de adquisición</label>
                <input id="valor_adquisicion" name="valor_adquisicion" type="number" step="0.01" value="{{ old('valor_adquisicion', $inventario->valor_adquisicion) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="codigo_qr">Código QR</label>
                <input id="codigo_qr" name="codigo_qr" value="{{ old('codigo_qr', $inventario->codigo_qr) }}">
            </div>
        </div>

        <div class="field">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $inventario->observaciones) }}</textarea>
        </div>

        <div class="row">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ route('inventarios.index') }}" class="btn secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
