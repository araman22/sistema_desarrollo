@extends('layouts.app')

@section('title', $policia->exists ? 'Editar agente' : 'Nuevo agente')

@section('content')
<div class="workspace-view view-form">
<div class="panel page-header" style="max-width: 900px; margin: 0 auto;">
    <h1>{{ $policia->exists ? 'Editar agente' : 'Nuevo agente' }}</h1>
    <form method="POST" action="{{ $policia->exists ? route('personal.update', $policia) : route('personal.store') }}" class="record-form">
        @csrf
        @if($policia->exists)
            @method('PUT')
        @endif

        <h2>Datos personales</h2>
        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:200px;">
                <label for="apellido">Apellido</label>
                <input id="apellido" name="apellido" value="{{ old('apellido', $persona->apellido) }}" maxlength="100" required>
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" value="{{ old('nombre', $persona->nombre) }}" maxlength="100" required>
            </div>
            <div class="field" style="flex:1; min-width:160px;">
                <label for="dni">DNI</label>
                <input id="dni" name="dni" value="{{ old('dni', $persona->dni) }}" maxlength="20" required>
            </div>
        </div>
        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:180px;">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento', $persona->fecha_nacimiento?->format('Y-m-d')) }}">
            </div>
            <div class="field" style="flex:1; min-width:180px;">
                <label for="estado_civil">Estado civil</label>
                <select id="estado_civil" name="estado_civil">
                    <option value="">Sin informar</option>
                    @foreach($estadosCiviles as $valor => $texto)
                        <option value="{{ $valor }}" {{ old('estado_civil', $persona->estado_civil) === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:180px;">
                <label for="sexo">Sexo</label>
                <input id="sexo" name="sexo" value="{{ old('sexo', $persona->sexo) }}" maxlength="20">
            </div>
        </div>
        <div class="field">
            <label for="direccion">Domicilio</label>
            <input id="direccion" name="direccion" value="{{ old('direccion', $persona->direccion) }}" maxlength="255">
        </div>
        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:200px;">
                <label for="telefono">Teléfono</label>
                <input id="telefono" name="telefono" value="{{ old('telefono', $persona->telefono) }}" maxlength="50">
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="correo_electronico">Correo electrónico</label>
                <input id="correo_electronico" name="correo_electronico" type="email" value="{{ old('correo_electronico', $persona->correo_electronico) }}" maxlength="150">
            </div>
        </div>

        <h2>Datos laborales</h2>
        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:160px;">
                <label for="numero_legajo">Legajo personal</label>
                <input id="numero_legajo" name="numero_legajo" value="{{ old('numero_legajo', $policia->numero_legajo) }}" maxlength="50" required>
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="jerarquia_id">Jerarquía</label>
                <select id="jerarquia_id" name="jerarquia_id" required>
                    <option value="">Seleccione</option>
                    @foreach($jerarquias as $jerarquia)
                        <option value="{{ $jerarquia->id }}" {{ (string) old('jerarquia_id', $policia->jerarquia_id) === (string) $jerarquia->id ? 'selected' : '' }}>{{ $jerarquia->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:180px;">
                <label for="fecha_ingreso">Fecha de ingreso a la Policía</label>
                <input id="fecha_ingreso" name="fecha_ingreso" type="date" value="{{ old('fecha_ingreso', $policia->fecha_ingreso?->format('Y-m-d')) }}" required>
            </div>
        </div>
        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:200px;">
                <label for="funcion_id">Función principal</label>
                <select id="funcion_id" name="funcion_id" required>
                    <option value="">Seleccione</option>
                    @foreach($funciones as $funcion)
                        <option value="{{ $funcion->id }}" {{ (string) old('funcion_id', $policia->funcion_id) === (string) $funcion->id ? 'selected' : '' }}>{{ $funcion->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:200px;">
                <label for="oficina_id">Oficina</label>
                <select id="oficina_id" name="oficina_id">
                    <option value="">Sin oficina</option>
                    @foreach($oficinas as $oficina)
                        <option value="{{ $oficina->id }}" {{ (string) old('oficina_id', $policia->oficina_id) === (string) $oficina->id ? 'selected' : '' }}>{{ $oficina->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:140px;">
                <label for="estado">Estado del legajo</label>
                <select id="estado" name="estado" required>
                    @foreach(['activo' => 'Activo', 'inactivo' => 'Inactivo'] as $valor => $texto)
                        <option value="{{ $valor }}" {{ old('estado', $policia->estado) === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @php($adicionales = array_map('strval', old('funciones_adicionales', $policia->exists ? $policia->funcionesAdicionales->pluck('id')->all() : [])))
        <fieldset class="field">
            <legend>Funciones adicionales</legend>
            <div class="row" style="gap:12px; flex-wrap:wrap;">
                @foreach($funciones as $funcion)
                    <label style="font-weight:normal;"><input type="checkbox" name="funciones_adicionales[]" value="{{ $funcion->id }}" {{ in_array((string) $funcion->id, $adicionales, true) ? 'checked' : '' }}> {{ $funcion->nombre }}</label>
                @endforeach
            </div>
        </fieldset>
        <div class="field">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3" maxlength="5000">{{ old('observaciones', $policia->observaciones) }}</textarea>
        </div>

        <div class="row form-actions">
            <button type="submit" class="action-button action-primary">Guardar</button>
            <a href="{{ $policia->exists ? route('personal.show', $policia) : route('personal.index') }}" class="action-button action-neutral">Cancelar</a>
        </div>
    </form>
</div>
</div>
@endsection
