<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inicio') · SIGDET</title>
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            @can('viewAny', App\Models\FondoFijoMovimiento::class)
                <a href="{{ route('fondo-fijo.index') }}">Fondo fijo</a>
            @endcan
            @can('viewAny', App\Models\Usuario::class)
                <a href="{{ route('usuarios.index') }}">Usuarios</a>
            @endcan
            @can('viewAny', App\Models\Rol::class)
                <a href="{{ route('roles.index') }}">Roles</a>
            @endcan
        </nav>
        @auth
            <div>
                <span>{{ auth()->user()->nombre_usuario }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline">
                    @csrf
                    <button type="submit">Cerrar sesión</button>
                </form>
            </div>
        @endauth
    </header>
    <div class="system-nav"><nav aria-label="Navegación principal">
        <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Inicio</a>
        @can('viewAny', App\Models\Oficina::class)<a href="{{ route('oficinas.index') }}" @if(request()->routeIs('oficinas.*')) aria-current="page" @endif>Oficinas</a>@endcan
        @can('viewAny', App\Models\Inventario::class)<a href="{{ route('inventarios.index') }}" @if(request()->routeIs('inventarios.*')) aria-current="page" @endif>Inventario</a>@endcan
        @can('viewAny', App\Models\EquipamientoTecnologico::class)<a href="{{ route('equipamientos-tecnologicos.index') }}" @if(request()->routeIs('equipamientos-tecnologicos.*')) aria-current="page" @endif>Equipamiento</a>@endcan
        @can('viewAny', App\Models\Documento::class)<a href="{{ route('documentos.index') }}" @if(request()->routeIs('documentos.*')) aria-current="page" @endif>Documentos</a>@endcan
        @can('viewAny', App\Models\DroneRobot::class)<a href="{{ route('movilidad.drones.index') }}" @if(request()->routeIs('movilidad.drones.*')) aria-current="page" @endif>Drones y robots</a>@endcan
        @can('viewAny', App\Models\Bateria::class)<a href="{{ route('movilidad.baterias.index') }}" @if(request()->routeIs('movilidad.baterias.*')) aria-current="page" @endif>Baterías</a>@endcan
        @can('viewAny', App\Models\FondoFijoMovimiento::class)<a href="{{ route('fondo-fijo.index') }}" @if(request()->routeIs('fondo-fijo.*')) aria-current="page" @endif>Fondo fijo</a>@endcan
        @can('viewAny', App\Models\Usuario::class)<a href="{{ route('usuarios.index') }}" @if(request()->routeIs('usuarios.*')) aria-current="page" @endif>Usuarios</a>@endcan
        @can('viewAny', App\Models\Rol::class)<a href="{{ route('roles.index') }}" @if(request()->routeIs('roles.*')) aria-current="page" @endif>Roles</a>@endcan
    </nav></div>
    @if(request()->routeIs('movilidad.*')) @include('movilidad.partials.navigation') @endif
    <main id="contenido">
        @include('partials.mensajes')

        @yield('content')
    </main>
</body>
</html>
