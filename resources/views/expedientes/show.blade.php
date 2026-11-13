@extends('layouts.app')

@section('title', 'Expediente '.$expediente->numero)

@section('content')
<div class="workspace-view view-show">
<div class="panel">
    <div class="row page-header" style="justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
            <h1>Expediente {{ $expediente->numero }}</h1>
            <p class="muted">{{ $expediente->bibliorato?->nombre }} · <span class="badge">{{ $expediente->estado_texto }}</span></p>
        </div>
        <div class="row">
            @can('update', $expediente)
                <a href="{{ route('expedientes.edit', $expediente) }}" class="action-button action-edit">Editar</a>
            @endcan
            @if($expediente->pases->isEmpty())
                @can('delete', $expediente)
                    <form method="POST" action="{{ route('expedientes.destroy', $expediente) }}" onsubmit="return confirm('¿Eliminar el expediente?');" class="record-action-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-button action-delete">Eliminar</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="field"><label>Asunto</label><p>{{ $expediente->asunto }}</p></div>
    <div class="grid">
        <div class="card"><strong>Origen:</strong> {{ $expediente->origen }}</div>
        <div class="card"><strong>Destino:</strong> {{ $expediente->destino }}</div>
        <div class="card"><strong>Emisión:</strong> {{ $expediente->fecha_emision->format('d/m/Y') }}</div>
        <div class="card"><strong>Recepción:</strong> {{ $expediente->fecha_recepcion?->format('d/m/Y') ?? '—' }}</div>
        <div class="card"><strong>Ubicación actual:</strong> {{ $expediente->ubicacion_actual }}</div>
        <div class="card"><strong>Responsable:</strong> {{ $expediente->responsable?->persona?->nombre_completo ?? 'Sin asignar' }}</div>
    </div>
    @if($expediente->observaciones)
        <div class="field" style="margin-top:16px;"><label>Observaciones</label><p>{{ $expediente->observaciones }}</p></div>
    @endif
</div>

<section class="panel" style="margin-top:20px;" aria-labelledby="documentos-heading">
    <h2 id="documentos-heading">Documentos asociados</h2>
    <ul>
        @forelse($expediente->documentos as $documento)
            <li><a href="{{ route('documentos.show', $documento) }}">{{ $documento->tipo?->nombre }} N° {{ $documento->numero }} · {{ $documento->nombre }}</a></li>
        @empty
            <li class="muted">Sin documentos asociados.</li>
        @endforelse
    </ul>
</section>

<section class="panel" style="margin-top:20px;" aria-labelledby="recorrido-heading">
    <h2 id="recorrido-heading">Recorrido y pases</h2>
    <div class="table-panel"><table>
        <thead><tr><th>#</th><th>Origen</th><th>Destino</th><th>Envío</th><th>Envía</th><th>Recepción</th><th>Recibe</th><th>Observaciones</th></tr></thead>
        <tbody>
            @forelse($expediente->pases as $pase)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pase->origen }}</td>
                    <td>{{ $pase->destino }}</td>
                    <td>{{ $pase->fecha_envio->format('d/m/Y H:i') }}<br><span class="muted">{{ $pase->usuarioEnvio?->nombre_usuario }}</span></td>
                    <td>{{ $pase->enviadoPor?->persona?->nombre_completo ?? '—' }}</td>
                    <td>
                        @if($pase->fecha_recepcion)
                            {{ $pase->fecha_recepcion->format('d/m/Y H:i') }}<br><span class="muted">{{ $pase->usuarioRecepcion?->nombre_usuario }}</span>
                        @else
                            <span class="badge">Pendiente de recepción</span>
                        @endif
                    </td>
                    <td>{{ $pase->recibidoPor?->persona?->nombre_completo ?? '—' }}</td>
                    <td>{{ $pase->observaciones ?? '' }}@if($pase->observaciones_recepcion)<br><span class="muted">Recepción: {{ $pase->observaciones_recepcion }}</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="8">Sin pases: el expediente permanece en {{ $expediente->ubicacion_actual }}.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @can('pasar', $expediente)
        @if($expediente->paseEnCurso)
            <form method="POST" action="{{ route('expedientes.pases.recibir', [$expediente, $expediente->paseEnCurso]) }}" style="margin-top:16px;" class="record-form">
                @csrf
                @method('PATCH')
                <h3>Registrar recepción en {{ $expediente->paseEnCurso->destino }}</h3>
                <div class="row" style="gap:12px; align-items:end;">
                    <div class="field" style="flex:1; min-width:200px;"><label for="fecha_recepcion">Fecha y hora</label><input id="fecha_recepcion" name="fecha_recepcion" type="datetime-local" value="{{ old('fecha_recepcion', now()->format('Y-m-d\TH:i')) }}" required></div>
                    <div class="field" style="flex:1; min-width:200px;">
                        <label for="recibido_por_id">Recibe</label>
                        <select id="recibido_por_id" name="recibido_por_id">
                            <option value="">Sin indicar</option>
                            @foreach($agentes as $agente)
                                <option value="{{ $agente->id }}" {{ (string) old('recibido_por_id') === (string) $agente->id ? 'selected' : '' }}>{{ $agente->persona?->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="flex:2; min-width:200px;"><label for="observaciones_recepcion">Observaciones</label><input id="observaciones_recepcion" name="observaciones_recepcion" value="{{ old('observaciones_recepcion') }}" maxlength="2000"></div>
                    <div class="field"><button type="submit" class="action-button action-primary">Registrar recepción</button></div>
                </div>
            </form>
        @elseif($expediente->estado === 'en_tramite')
            <form method="POST" action="{{ route('expedientes.pases.store', $expediente) }}" style="margin-top:16px;" class="record-form">
                @csrf
                <h3>Nuevo pase desde {{ $expediente->ubicacion_actual }}</h3>
                <div class="row" style="gap:12px; align-items:end;">
                    <div class="field" style="flex:1; min-width:180px;"><label for="destino">Destino</label><input id="destino" name="destino" value="{{ old('destino') }}" maxlength="150" required></div>
                    <div class="field" style="flex:1; min-width:200px;"><label for="fecha_envio">Fecha y hora de envío</label><input id="fecha_envio" name="fecha_envio" type="datetime-local" value="{{ old('fecha_envio', now()->format('Y-m-d\TH:i')) }}" required></div>
                    <div class="field" style="flex:1; min-width:200px;">
                        <label for="enviado_por_id">Envía</label>
                        <select id="enviado_por_id" name="enviado_por_id">
                            <option value="">Sin indicar</option>
                            @foreach($agentes as $agente)
                                <option value="{{ $agente->id }}" {{ (string) old('enviado_por_id', $expediente->responsable_id) === (string) $agente->id ? 'selected' : '' }}>{{ $agente->persona?->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="flex:2; min-width:200px;"><label for="pase_observaciones">Observaciones</label><input id="pase_observaciones" name="observaciones" value="{{ old('observaciones') }}" maxlength="2000"></div>
                    <div class="field"><button type="submit" class="action-button action-primary">Enviar pase</button></div>
                </div>
            </form>
        @endif
    @endcan
</section>

<div class="row" style="margin-top:20px;">
    <a href="{{ route('expedientes.index') }}" class="action-button action-neutral">Volver al listado</a>
</div>
</div>
@endsection
