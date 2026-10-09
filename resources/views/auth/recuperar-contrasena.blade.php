<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>
</head>
<body>
    <h1>Recuperar contraseña</h1>

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <p>Ingresá el correo de tu cuenta y te enviaremos un enlace para restablecer la contraseña.</p>

    <form action="{{ route('password.email') }}" method="POST">
        @csrf
        <div>
            <label for="correo_electronico">Correo electrónico:</label>
            <input type="email" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" maxlength="150" required autofocus autocomplete="email">
            @error('correo_electronico')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <button type="submit">Enviar enlace</button>
    </form>

    <p><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
</body>
</html>
