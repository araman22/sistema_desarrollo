<div class="module-bar">
    <nav class="module-nav" aria-label="Navegación de movilidad">
        <span class="module-label">Movilidad y mantenimiento</span>
        @can('viewAny', App\Models\DroneRobot::class)
            <a href="{{ route('movilidad.drones.index') }}" @if(request()->routeIs('movilidad.drones.*')) aria-current="page" @endif>Drones y robots</a>
        @endcan
        @can('viewAny', App\Models\Bateria::class)
            <a href="{{ route('movilidad.baterias.index') }}" @if(request()->routeIs('movilidad.baterias.*')) aria-current="page" @endif>Baterías</a>
        @endcan
    </nav>
</div>
