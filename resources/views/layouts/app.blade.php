<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inicio') · SIGDET</title>
    @if(request()->routeIs('movilidad.*'))
        <link rel="stylesheet" href="{{ asset('css/sigdet.css') }}">
    @endif
</head>
<body @class(['movilidad-page' => request()->routeIs('movilidad.*')])>
    <header>
        @if(request()->routeIs('movilidad.*'))
            <a class="app-brand" href="{{ route('dashboard') }}"><span class="brand-mark" aria-hidden="true">S</span><span>SIGDET<small>Gestión institucional</small></span></a>
        @endif
        <nav>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            @unless(request()->routeIs('movilidad.*'))
                @can('viewAny', App\Models\DroneRobot::class)
                    <a href="{{ route('movilidad.drones.index') }}">Drones y robots</a>
                @endcan
                @can('viewAny', App\Models\Bateria::class)
                    <a href="{{ route('movilidad.baterias.index') }}">Baterías</a>
                @endcan
            @endunless
            @can('viewAny', App\Models\CajaChicaMovimiento::class)
                <a href="{{ route('caja-chica.index') }}">Caja chica</a>
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

    @if(request()->routeIs('movilidad.*'))
        @include('movilidad.partials.navigation')
    @endif

    <main>
        @include('partials.mensajes')
        @if(request()->routeIs('movilidad.*') && session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
