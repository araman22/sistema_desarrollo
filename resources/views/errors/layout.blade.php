{{-- Layout de errores: no depende de layouts.app porque también lo ven invitados. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') · SIGDET</title>
</head>
<body>
    <main>
        <p>Error @yield('code')</p>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>

        @auth
            <a href="{{ route('dashboard') }}">Volver al inicio</a>
        @else
            <a href="{{ route('login') }}">Ir a iniciar sesión</a>
        @endauth
    </main>
</body>
</html>
