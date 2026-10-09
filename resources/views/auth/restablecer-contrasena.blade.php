<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
</head>
<body>
    <h1>Restablecer contraseña</h1>

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        @error('token')
            <p>{{ $message }}</p>
        @enderror

        <div>
            <label for="correo_electronico">Correo electrónico:</label>
            <input type="email" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico', $correo) }}" maxlength="150" required autocomplete="email">
            @error('correo_electronico')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password">Nueva contraseña:</label>
            <input type="password" id="password" name="password" required autofocus autocomplete="new-password">
            @error('password')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password_confirmation">Confirmar contraseña:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
        </div>
        <button type="submit">Restablecer contraseña</button>
    </form>

    <p><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
</body>
</html>
