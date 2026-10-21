<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inicio') · SIGDET</title>
    <link rel="stylesheet" href="{{ asset('css/sigdet.css') }}?v={{ filemtime(public_path('css/sigdet.css')) }}">
</head>
<body @class(['sigdet-app', 'movilidad-page' => request()->routeIs('movilidad.*')])>
    <a class="skip-link" href="#contenido">Ir al contenido</a>
    <header>
        <a class="app-brand" href="{{ route('dashboard') }}"><span class="brand-mark" aria-hidden="true">S</span><span>SIGDET<small>Gestión institucional</small></span></a>
        <span class="app-context">Departamento de Desarrollo Tecnológico</span>
        @auth
            <div><span>{{ auth()->user()->nombre_usuario }}</span><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Cerrar sesión</button></form></div>
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
        @can('viewAny', App\Models\CajaChicaMovimiento::class)<a href="{{ route('caja-chica.index') }}" @if(request()->routeIs('caja-chica.*')) aria-current="page" @endif>Caja chica</a>@endcan
        @can('viewAny', App\Models\Usuario::class)<a href="{{ route('usuarios.index') }}" @if(request()->routeIs('usuarios.*')) aria-current="page" @endif>Usuarios</a>@endcan
        @can('viewAny', App\Models\Rol::class)<a href="{{ route('roles.index') }}" @if(request()->routeIs('roles.*')) aria-current="page" @endif>Roles</a>@endcan
    </nav></div>
    @if(request()->routeIs('movilidad.*')) @include('movilidad.partials.navigation') @endif
    <main id="contenido">
        @include('partials.mensajes')
        @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
        @yield('content')
    </main>
    <footer class="app-footer">SIGDET <span aria-hidden="true">·</span> Sistema Integral de Gestión del Departamento de Desarrollo Tecnológico</footer>
</body>
</html>
