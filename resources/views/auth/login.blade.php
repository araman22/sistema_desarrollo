<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
</head>
<body>
    <h1>Iniciar sesión</h1>
    <form action="{{ route('login.store') }}" method="POST">
        @csrf
        <div>
            <label for="nombre_usuario">Usuario:</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" maxlength="100" required autofocus autocomplete="username">
            @error('nombre_usuario')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            @error('password')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label>
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Recordarme
            </label>
            @error('remember')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
