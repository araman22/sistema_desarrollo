@extends('movilidad.layout')
@section('content')
<div class="workspace-view view-form"><h1>{{ $tipo->exists?'Editar':'Registrar' }} tipo de vehículo</h1><form class="card record-form" method="post" action="{{ $tipo->exists?route('tipos-vehiculos.update',$tipo):route('tipos-vehiculos.store') }}">@csrf @if($tipo->exists)@method('PUT')@endif<label for="nombre">Nombre *</label><input id="nombre" name="nombre" value="{{ old('nombre',$tipo->nombre) }}" maxlength="100" required><div class="actions form-actions"><button class="action-button action-primary">Guardar</button><a href="{{ route('tipos-vehiculos.index') }}" class="action-button action-neutral">Cancelar</a></div></form></div>
@endsection
