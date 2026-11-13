@extends('movilidad.layout')
@section('content')
<div class="workspace-view view-show"><h1>{{ $tipo->nombre }}</h1><div class="card"><p>Vehículos de este tipo: {{ $tipo->vehiculos()->count() }}</p><a href="{{ route('tipos-vehiculos.index') }}" class="action-button action-neutral">Volver</a>@can('delete',$tipo)<form class="actions record-action-form" method="post" action="{{ route('tipos-vehiculos.destroy',$tipo) }}" onsubmit="return confirm('¿Eliminar este tipo?')">@csrf @method('DELETE')<button class="action-button action-delete">Eliminar tipo</button></form>@endcan</div></div>
@endsection
