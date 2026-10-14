<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title', 'SIGDET') · SIGDET</title><link rel="stylesheet" href="{{ asset('css/sigdet.css') }}"></head>
<body>
<header><a href="{{ url('/') }}"><strong>SIGDET</strong></a><nav>
    @can('viewAny', App\Models\DroneRobot::class)<a href="{{ route('movilidad.drones.index') }}">Drones y robots</a>@endcan
    @can('viewAny', App\Models\Bateria::class)<a href="{{ route('movilidad.baterias.index') }}">Baterías</a>@endcan
    @yield('navigation')
</nav>@auth<span>{{ auth()->user()->nombre_usuario }}</span>@endauth</header>
<main><x-feedback />@yield('content')</main>
</body></html>
