@if(in_array($campo, ['tipo','estado','responsable_id','dron_robot_id']))
    <div><label for="{{ $campo }}">{{ $label }}{{ $campo !== 'responsable_id' ? ' *' : '' }}</label><select id="{{ $campo }}" name="{{ $campo }}" @required($campo !== 'responsable_id')>
    @if($campo === 'responsable_id')
        <option value="">Sin responsable</option>@foreach($responsables as $responsable)<option value="{{ $responsable->id }}" @selected((string)old($campo, $recurso->$campo) === (string)$responsable->id)>{{ $responsable->numero_legajo }} · {{ $responsable->nombre_completo }}</option>@endforeach
    @elseif($campo === 'dron_robot_id')
        <option value="">Seleccioná un recurso</option>@foreach($drones as $drone)<option value="{{ $drone->id }}" @selected((string)old($campo, $recurso->$campo) === (string)$drone->id)>{{ $drone->codigo }} · {{ $drone->nombre }} ({{ str_replace('_', ' ', $drone->estado) }})</option>@endforeach
    @else
        @foreach($campo === 'tipo' ? $modelo::TIPOS : $modelo::ESTADOS as $opcion)<option value="{{ $opcion }}" @selected(old($campo, $recurso->$campo) === $opcion)>{{ ucfirst(str_replace('_', ' ', $opcion)) }}</option>@endforeach
    @endif
    </select>@error($campo)<span class="field-error">{{ $message }}</span>@enderror</div>
@elseif(in_array($campo,['horas_uso','ciclos']))
    <x-field :name="$campo" :label="$label" :value="$recurso->$campo ?? 0" type="number" :required="true" :min="$recurso->$campo ?? 0" :step="$campo === 'ciclos' ? 1 : 0.01" :max="$campo === 'ciclos' ? 4294967295 : 99999999.99" />
@elseif(in_array($campo,['fecha_adquisicion','ultima_revision']))
    <x-field :name="$campo" :label="$label" :value="$recurso->$campo?->toDateString()" type="date" :max="today()->toDateString()" />
@elseif($campo === 'observaciones')
    <x-field :name="$campo" :label="$label" :value="$recurso->$campo" type="textarea" maxlength="5000" placeholder="Información adicional relevante para la gestión del recurso." />
@else
    <x-field :name="$campo" :label="$label" :value="$recurso->$campo" :required="in_array($campo,['nombre','codigo'])" :maxlength="$campo === 'nombre' ? 150 : (in_array($campo,['codigo','capacidad']) ? 50 : 100)" />
@endif
