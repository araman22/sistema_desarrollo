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
            @can('viewAny', App\Models\Usuario::class)
                <a href="{{ route('usuarios.index') }}">Usuarios</a>
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

    <main>
        @include('partials.mensajes')

        @yield('content')
    </main>
</body>
</html>
