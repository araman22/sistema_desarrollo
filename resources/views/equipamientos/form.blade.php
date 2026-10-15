@extends('layouts.app')

@section('title', isset($equipamiento->id) ? 'Editar equipo' : 'Nuevo equipo')

@section('content')
<div class="panel" style="max-width: 900px; margin: 0 auto;">
    <h1>{{ isset($equipamiento->id) ? 'Editar equipo' : 'Nuevo equipo' }}</h1>
    <form method="POST" action="{{ isset($equipamiento->id) ? route('equipamientos-tecnologicos.update', $equipamiento) : route('equipamientos-tecnologicos.store') }}">
        @csrf
        @if(isset($equipamiento->id))
            @method('PUT')
        @endif

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="codigo">Código</label>
                <input id="codigo" name="codigo" value="{{ old('codigo', $equipamiento->codigo) }}" required>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" required>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" {{ old('tipo', $equipamiento->tipo) === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ old('estado', $equipamiento->estado) === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$estado)) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" value="{{ old('nombre', $equipamiento->nombre) }}" required>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="marca">Marca</label>
                <input id="marca" name="marca" value="{{ old('marca', $equipamiento->marca) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="modelo">Modelo</label>
                <input id="modelo" name="modelo" value="{{ old('modelo', $equipamiento->modelo) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="numero_serie">Número de serie</label>
                <input id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $equipamiento->numero_serie) }}">
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="procesador">Procesador</label>
                <input id="procesador" name="procesador" value="{{ old('procesador', $equipamiento->procesador) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="memoria_ram">Memoria RAM</label>
                <input id="memoria_ram" name="memoria_ram" value="{{ old('memoria_ram', $equipamiento->memoria_ram) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="almacenamiento">Almacenamiento</label>
                <input id="almacenamiento" name="almacenamiento" value="{{ old('almacenamiento', $equipamiento->almacenamiento) }}">
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="sistema_operativo">Sistema operativo</label>
                <input id="sistema_operativo" name="sistema_operativo" value="{{ old('sistema_operativo', $equipamiento->sistema_operativo) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="direccion_ip">Dirección IP</label>
                <input id="direccion_ip" name="direccion_ip" value="{{ old('direccion_ip', $equipamiento->direccion_ip) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="direccion_mac">Dirección MAC</label>
                <input id="direccion_mac" name="direccion_mac" value="{{ old('direccion_mac', $equipamiento->direccion_mac) }}">
            </div>
        </div>

        <div class="row" style="gap:16px;">
            <div class="field" style="flex:1; min-width:220px;">
                <label for="oficina_id">Oficina</label>
                <select id="oficina_id" name="oficina_id">
                    <option value="">Sin oficina</option>
                    @foreach($oficinas as $oficina)
                        <option value="{{ $oficina->id }}" {{ old('oficina_id', $equipamiento->oficina_id) == $oficina->id ? 'selected' : '' }}>{{ $oficina->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="fecha_adquisicion">Fecha de adquisición</label>
                <input id="fecha_adquisicion" name="fecha_adquisicion" type="date" value="{{ old('fecha_adquisicion', optional($equipamiento->fecha_adquisicion)->format('Y-m-d')) }}">
            </div>
            <div class="field" style="flex:1; min-width:220px;">
                <label for="codigo_qr">Código QR</label>
                <input id="codigo_qr" name="codigo_qr" value="{{ old('codigo_qr', $equipamiento->codigo_qr) }}">
            </div>
        </div>

        <div class="field">
            <label for="observaciones">Observaciones</label>
            <textarea id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $equipamiento->observaciones) }}</textarea>
        </div>

        <div class="row">
            <button type="submit" class="btn">Guardar</button>
            <a href="{{ route('equipamientos-tecnologicos.index') }}" class="btn secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection
