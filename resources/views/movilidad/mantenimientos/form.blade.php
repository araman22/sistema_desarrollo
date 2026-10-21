@extends('layouts.app')
@section('title', 'Mantenimiento de '.$recurso->codigo)
@section('content')
<nav class="breadcrumbs" aria-label="Ubicación"><span>Movilidad</span><span aria-hidden="true">/</span><a href="{{ route($prefijo.'index', $recurso) }}">{{ $recurso->codigo }} · Mantenimientos</a><span aria-hidden="true">/</span><span aria-current="page">{{ $mantenimiento->exists ? 'Editar' : 'Nuevo registro' }}</span></nav>
<x-movilidad.page-header :title="($mantenimiento->exists ? 'Editar mantenimiento' : 'Registrar mantenimiento')" :description="'Recurso '.$recurso->codigo.'. Los campos marcados con * son obligatorios.'" />
<form class="card form-card" method="POST" action="{{ $mantenimiento->exists ? route($prefijo.'update', [$recurso, $mantenimiento]) : route($prefijo.'store', $recurso) }}">
@csrf @if($mantenimiento->exists) @method('PUT') @endif
<section class="form-section"><div class="section-heading"><h2>Datos de la intervención</h2><p>Clasificación, fecha y situación del trabajo.</p></div><div class="grid">
    @foreach(['tipo'=>'Tipo de mantenimiento','estado'=>'Estado del trabajo'] as $campo=>$label)
        <div><label for="{{ $campo }}">{{ $label }} *</label><select id="{{ $campo }}" name="{{ $campo }}" required>@foreach($campo === 'tipo' ? App\Models\Mantenimiento::TIPOS_MOVILIDAD : App\Models\Mantenimiento::ESTADOS_MOVILIDAD as $valor)<option value="{{ $valor }}" @selected(old($campo,$mantenimiento->$campo) === $valor)>{{ $valor === 'revision' ? 'Revisión' : ucfirst(str_replace('_',' ',$valor)) }}</option>@endforeach</select>@error($campo)<span class="field-error">{{ $message }}</span>@enderror</div>
    @endforeach
    <x-field name="fecha" label="Fecha de intervención" type="date" :value="$mantenimiento->fecha?->toDateString()" :required="true" />
    <x-field name="costo" label="Costo" type="number" :value="$mantenimiento->costo" min="0" max="9999999999999.99" step="0.01" placeholder="0,00" />
    <div class="full-width"><label for="responsable_id">Responsable</label><select id="responsable_id" name="responsable_id"><option value="">Sin responsable</option>@foreach($responsables as $responsable)<option value="{{ $responsable->id }}" @selected((string)old('responsable_id',$mantenimiento->responsable_id) === (string)$responsable->id)>{{ $responsable->numero_legajo }} · {{ $responsable->nombre_completo }}</option>@endforeach</select>@error('responsable_id')<span class="field-error">{{ $message }}</span>@enderror</div>
</div></section>
<section class="form-section"><div class="section-heading"><h2>Detalle y resultado</h2><p>Describí el trabajo y las conclusiones de la intervención.</p></div><div class="grid">
    <div class="full-width"><x-field name="descripcion" label="Descripción del trabajo" type="textarea" :value="$mantenimiento->descripcion" :required="true" maxlength="5000" placeholder="Indicá las tareas de revisión o mantenimiento." /></div>
    <x-field name="resultado" label="Resultado" type="textarea" :value="$mantenimiento->resultado" maxlength="5000" placeholder="Obligatorio cuando el trabajo está realizado." />
    <x-field name="observaciones" label="Observaciones" type="textarea" :value="$mantenimiento->observaciones" maxlength="5000" placeholder="Información adicional, si corresponde." />
</div></section>
<div class="form-footer"><p>Un trabajo realizado requiere resultado y fecha no futura. Las intervenciones realizadas o canceladas conservan su historial.</p><div class="actions"><a class="button secondary" href="{{ route($prefijo.'index', $recurso) }}">Cancelar</a><button type="submit">Guardar mantenimiento</button></div></div>
</form>
@endsection
