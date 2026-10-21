@extends('layouts.app')
@section('title', 'Mantenimiento de '.$recurso->codigo)
@section('content')
<h1>{{ $mantenimiento->exists ? 'Editar mantenimiento' : 'Nuevo mantenimiento' }} · {{ $recurso->codigo }}</h1>
<form class="card" method="POST" action="{{ $mantenimiento->exists ? route($prefijo.'update', [$recurso, $mantenimiento]) : route($prefijo.'store', $recurso) }}">@csrf @if($mantenimiento->exists) @method('PUT') @endif
<div class="grid">
@foreach(['tipo'=>'Tipo *','estado'=>'Estado *'] as $campo=>$label)<div><label for="{{ $campo }}">{{ $label }}</label><select id="{{ $campo }}" name="{{ $campo }}" required>@foreach($campo === 'tipo' ? App\Models\Mantenimiento::TIPOS_MOVILIDAD : App\Models\Mantenimiento::ESTADOS_MOVILIDAD as $valor)<option value="{{ $valor }}" @selected(old($campo,$mantenimiento->$campo) === $valor)>{{ str_replace('_',' ',ucfirst($valor)) }}</option>@endforeach</select></div>@endforeach
<x-field name="fecha" label="Fecha" type="date" :value="$mantenimiento->fecha?->toDateString()" :required="true" />
<x-field name="costo" label="Costo" type="number" :value="$mantenimiento->costo" min="0" max="9999999999999.99" step="0.01" />
<div><label for="responsable_id">Responsable</label><select id="responsable_id" name="responsable_id"><option value="">Sin responsable</option>@foreach($responsables as $responsable)<option value="{{ $responsable->id }}" @selected((string)old('responsable_id',$mantenimiento->responsable_id) === (string)$responsable->id)>{{ $responsable->numero_legajo }} · {{ $responsable->nombre_completo }}</option>@endforeach</select></div>
@foreach(['descripcion'=>'Descripción','resultado'=>'Resultado','observaciones'=>'Observaciones'] as $campo=>$label)<x-field :name="$campo" :label="$label" type="textarea" :value="$mantenimiento->$campo" :required="$campo === 'descripcion'" maxlength="5000" />@endforeach
</div><p class="muted">Un trabajo realizado requiere resultado y fecha no futura. Los trabajos realizados o cancelados se conservan sin edición ni eliminación. Una revisión realizada actualiza la fecha de última revisión de la batería.</p><div class="actions"><button>Guardar mantenimiento</button><a href="{{ route($prefijo.'index', $recurso) }}">Volver</a></div></form>
@endsection
