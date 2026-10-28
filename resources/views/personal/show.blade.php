@extends('layouts.app')

@section('title', 'Legajo de '.$policia->persona?->nombre_completo)

@section('content')
<div class="panel">
    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>{{ $policia->jerarquia?->nombre }} {{ $policia->persona?->nombre_completo }}</h1>
            <p class="muted">Legajo {{ $policia->numero_legajo ?? '—' }} · DNI {{ $policia->persona?->dni }} · Situación actual: <strong>{{ $situacionActual }}</strong></p>
        </div>
        <div class="row">
            @can('update', $policia)
                <a href="{{ route('personal.edit', $policia) }}" class="action-button action-edit">Editar</a>
            @endcan
            @can('delete', $policia)
                <form method="POST" action="{{ route('personal.destroy', $policia) }}" onsubmit="return confirm('¿Desea eliminar el agente?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-button action-delete">Eliminar</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid">
        <div class="card"><strong>Fecha de ingreso:</strong> {{ $policia->fecha_ingreso?->format('d/m/Y') ?? '—' }}</div>
        <div class="card"><strong>Antigüedad:</strong> {{ $policia->antiguedad_texto }}</div>
        <div class="card"><strong>Oficina:</strong> {{ $policia->oficina?->nombre ?? 'Sin oficina' }}</div>
        <div class="card"><strong>Estado del legajo:</strong> {{ ucfirst($policia->estado) }}</div>
        <div class="card"><strong>Función principal:</strong> {{ $policia->funcion?->nombre ?? '—' }}</div>
        <div class="card"><strong>Funciones adicionales:</strong> {{ $policia->funcionesAdicionales->pluck('nombre')->implode(', ') ?: 'Ninguna' }}</div>
        <div class="card"><strong>Domicilio:</strong> {{ $policia->persona?->direccion ?? '—' }}</div>
        <div class="card"><strong>Estado civil:</strong> {{ App\Models\Persona::ESTADOS_CIVILES[$policia->persona?->estado_civil] ?? 'Sin informar' }}</div>
    </div>
</div>

<section class="panel" style="margin-top:20px;" aria-labelledby="familia-heading">
    <h2 id="familia-heading">Cónyuge e hijos</h2>
    <table>
        <thead><tr><th>Parentesco</th><th>Nombre</th><th>DNI</th><th>Nacimiento</th><th></th></tr></thead>
        <tbody>
            @forelse($policia->familiares->sortBy(fn ($f) => [$f->parentesco !== 'conyuge', $f->fecha_nacimiento]) as $familiar)
                <tr>
                    <td>{{ App\Models\Familiar::PARENTESCOS[$familiar->parentesco] }}</td>
                    <td>{{ $familiar->nombre_completo }}</td>
                    <td>{{ $familiar->dni ?? '—' }}</td>
                    <td>{{ $familiar->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        @can('update', $policia)
                            <form method="POST" action="{{ route('personal.familiares.destroy', [$policia, $familiar]) }}" onsubmit="return confirm('¿Eliminar familiar?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-button action-delete">Quitar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Sin familiares registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    @can('update', $policia)
        <form method="POST" action="{{ route('personal.familiares.store', $policia) }}" style="margin-top:16px;">
            @csrf
            <div class="row" style="gap:12px; align-items:end;">
                <div class="field" style="flex:1; min-width:140px;">
                    <label for="parentesco">Parentesco</label>
                    <select id="parentesco" name="parentesco" required>
                        @foreach(App\Models\Familiar::PARENTESCOS as $valor => $texto)
                            <option value="{{ $valor }}" {{ old('parentesco') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="flex:1; min-width:150px;"><label for="familiar_apellido">Apellido</label><input id="familiar_apellido" name="apellido" value="{{ old('apellido') }}" maxlength="100" required></div>
                <div class="field" style="flex:1; min-width:150px;"><label for="familiar_nombre">Nombre</label><input id="familiar_nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100" required></div>
                <div class="field" style="flex:1; min-width:120px;"><label for="familiar_dni">DNI</label><input id="familiar_dni" name="dni" value="{{ old('dni') }}" maxlength="20"></div>
                <div class="field" style="flex:1; min-width:150px;"><label for="familiar_nacimiento">Nacimiento</label><input id="familiar_nacimiento" name="fecha_nacimiento" type="date" value="{{ old('fecha_nacimiento') }}"></div>
                <div class="field"><button type="submit" class="btn">Agregar</button></div>
            </div>
        </form>
    @endcan
</section>

<section class="panel" style="margin-top:20px;" aria-labelledby="licencias-heading">
    <div class="row" style="justify-content:space-between; align-items:center;">
        <h2 id="licencias-heading">Licencias</h2>
        @can('create', App\Models\Licencia::class)
            <a href="{{ route('licencias.create', ['policia_id' => $policia->id]) }}" class="btn">Nueva licencia</a>
        @endcan
    </div>
    <form method="GET" class="row" style="gap:12px; align-items:end;">
        <div class="field"><label for="anio">Cupo anual del año</label><input id="anio" name="anio" type="number" min="1980" max="{{ now()->year + 1 }}" value="{{ $cupo['anio'] }}"></div>
        <div class="field"><button type="submit" class="btn secondary">Consultar</button></div>
    </form>
    <div class="grid">
        <div class="card"><strong>Cupo {{ $cupo['anio'] }}:</strong> {{ $cupo['cupo'] }} días corridos</div>
        <div class="card"><strong>Utilizados:</strong> {{ $cupo['utilizados'] }} días</div>
        <div class="card"><strong>Disponibles:</strong> {{ $cupo['disponibles'] }} días</div>
    </div>
    <table style="margin-top:16px;">
        <thead><tr><th>Tipo</th><th>Año</th><th>Salida</th><th>Días</th><th>Regreso previsto</th><th>Regreso efectivo</th><th>Reemplazo</th><th>Estado</th><th></th></tr></thead>
        <tbody>
            @forelse($licencias as $licencia)
                <tr>
                    <td>{{ $licencia->tipo?->nombre ?? '—' }}</td>
                    <td>{{ $licencia->anio_correspondiente }}</td>
                    <td>{{ $licencia->fecha_inicio->format('d/m/Y') }}</td>
                    <td>{{ $licencia->cantidad_dias }}</td>
                    <td>{{ $licencia->fecha_regreso_prevista?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $licencia->fecha_regreso_efectivo?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $licencia->requiere_reemplazo ? ($licencia->reemplazo?->persona?->nombre_completo ?? '—') : 'No requiere' }}</td>
                    <td><span class="badge">{{ $licencia->estado_texto }}</span></td>
                    <td><a href="{{ route('licencias.show', $licencia) }}" class="action-button action-view">Ver</a></td>
                </tr>
            @empty
                <tr><td colspan="9">Sin licencias registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

<section class="panel" style="margin-top:20px;" aria-labelledby="revista-heading">
    <h2 id="revista-heading">Situación de revista e historial de novedades</h2>
    <table>
        <thead><tr><th>Situación</th><th>Desde</th><th>Hasta</th><th>Origen</th><th>Observaciones</th><th>Registrado por</th></tr></thead>
        <tbody>
            @forelse($policia->novedades as $novedad)
                <tr>
                    <td>{{ $novedad->situacion_texto }}</td>
                    <td>{{ $novedad->fecha_desde->format('d/m/Y') }}</td>
                    <td>{{ $novedad->fecha_hasta?->format('d/m/Y') ?? 'En curso' }}</td>
                    <td>@if($novedad->licencia)<a href="{{ route('licencias.show', $novedad->licencia) }}">Licencia</a>@else Novedad manual @endif</td>
                    <td>{{ $novedad->observaciones ?? '—' }}</td>
                    <td>{{ $novedad->usuario?->nombre_usuario ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Sin novedades: el agente figura activo.</td></tr>
            @endforelse
        </tbody>
    </table>
    @can('update', $policia)
        <form method="POST" action="{{ route('personal.novedades.store', $policia) }}" style="margin-top:16px;">
            @csrf
            <div class="row" style="gap:12px; align-items:end;">
                <div class="field" style="flex:1; min-width:160px;">
                    <label for="situacion">Nueva situación</label>
                    <select id="situacion" name="situacion" required>
                        @foreach($situacionesManuales as $valor => $texto)
                            <option value="{{ $valor }}" {{ old('situacion') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="flex:1; min-width:150px;"><label for="fecha_desde">Desde</label><input id="fecha_desde" name="fecha_desde" type="date" value="{{ old('fecha_desde', today()->format('Y-m-d')) }}" required></div>
                <div class="field" style="flex:1; min-width:150px;"><label for="fecha_hasta">Hasta (opcional)</label><input id="fecha_hasta" name="fecha_hasta" type="date" value="{{ old('fecha_hasta') }}"></div>
                <div class="field" style="flex:2; min-width:200px;"><label for="novedad_observaciones">Observaciones</label><input id="novedad_observaciones" name="observaciones" value="{{ old('observaciones') }}" maxlength="2000"></div>
                <div class="field"><button type="submit" class="btn">Registrar</button></div>
            </div>
        </form>
    @endcan
</section>

<div class="row" style="margin-top:20px;">
    <a href="{{ route('personal.index') }}" class="btn secondary">Volver al listado</a>
</div>
@endsection
