<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Iniciar sesión · SIGDET</title><link rel="stylesheet" href="{{ asset('css/sigdet.css') }}?v={{ filemtime(public_path('css/sigdet.css')) }}"></head>
<body class="sigdet-app auth-page">
<main class="auth-container"><div class="auth-brand"><span class="brand-mark" aria-hidden="true">S</span><div><strong>SIGDET</strong><span>Gestión institucional</span></div></div>
<section class="auth-card"><p class="eyebrow">Acceso al sistema</p><h1>Iniciar sesión</h1><p class="page-description">Ingresá con tu cuenta institucional para continuar.</p>
@if(session('status'))<p class="notice" role="status">{{ session('status') }}</p>@endif
@if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif
<form action="{{ route('login.store') }}" method="POST">@csrf
    <div><label for="nombre_usuario">Usuario</label><input type="text" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario') }}" maxlength="100" required autofocus autocomplete="username">@error('nombre_usuario')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div><label for="password">Contraseña</label><input type="password" id="password" name="password" required autocomplete="current-password">@error('password')<p class="field-error">{{ $message }}</p>@enderror</div>
    <div class="auth-options"><label><input type="checkbox" name="remember" value="1" @checked(old('remember'))>Recordarme</label><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></div>
    @error('remember')<p class="field-error">{{ $message }}</p>@enderror
    <button type="submit">Ingresar al sistema</button>
</form></section><p class="auth-caption">Departamento de Desarrollo Tecnológico</p></main>
</body></html>
