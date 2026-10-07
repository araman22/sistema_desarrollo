@extends('layouts.app')

@section('title', 'Login - SIGDET')

@section('content')
<div class="panel" style="max-width: 520px; margin: 80px auto;">
    <h1>Iniciar sesión</h1>
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="field">
            <label for="login">Usuario o correo electrónico</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required>
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" required>
        </div>

        <div class="field">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="remember" value="1">
                Mantener sesión abierta
            </label>
        </div>

        <button type="submit" class="btn">Ingresar</button>
    </form>
</div>
@endsection