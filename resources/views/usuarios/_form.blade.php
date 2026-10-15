{{-- Compartido por create y edit. $usuario es null al crear. --}}
@php
    $esPropio = $usuario !== null && $usuario->is(auth()->user());
    $mostrarContrasena = $usuario === null || auth()->user()->can('restablecerContrasena', $usuario);
@endphp

<div>
    <label for="nombre_usuario">Nombre de usuario:</label>
    <input type="text" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario?->nombre_usuario) }}" maxlength="100" required>
    @error('nombre_usuario')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="correo_electronico">Correo electrónico:</label>
    <input type="email" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico', $usuario?->correo_electronico) }}" maxlength="150">
    @error('correo_electronico')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="rol_id">Rol:</label>
    @if ($esPropio)
        {{-- No se puede cambiar el rol propio: se envía el actual. --}}
        <input type="hidden" name="rol_id" value="{{ $usuario->rol_id }}">
        <span>{{ $usuario->rol?->nombre }} (no podés cambiar tu propio rol)</span>
    @else
        <select id="rol_id" name="rol_id" required>
            <option value="">Seleccioná un rol</option>
            @foreach ($roles as $rol)
                <option value="{{ $rol->id }}" @selected((string) old('rol_id', $usuario?->rol_id) === (string) $rol->id)>{{ $rol->nombre }}</option>
            @endforeach
        </select>
    @endif
    @error('rol_id')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="policia_id">Policía asociado:</label>
    <select id="policia_id" name="policia_id">
        <option value="">Ninguno</option>
        @foreach ($policias as $policia)
            <option value="{{ $policia->id }}" @selected((string) old('policia_id', $usuario?->policia_id) === (string) $policia->id)>
                {{ $policia->numero_legajo }} — {{ $policia->nombre_completo }}
            </option>
        @endforeach
    </select>
    @error('policia_id')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    @if ($esPropio)
        <input type="hidden" name="activo" value="{{ $usuario->activo ? 1 : 0 }}">
        <span>Activo (no podés desactivar tu propio usuario)</span>
    @else
        <input type="hidden" name="activo" value="0">
        <label>
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario?->activo ?? true))>
            Activo
        </label>
    @endif
    @error('activo')
        <p>{{ $message }}</p>
    @enderror
</div>

@if ($mostrarContrasena)
    <div>
        <label for="contrasena">Contraseña{{ $usuario ? ' (dejala vacía para no cambiarla)' : '' }}:</label>
        <input type="password" id="contrasena" name="contrasena" autocomplete="new-password" @required($usuario === null)>
        @error('contrasena')
            <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="contrasena_confirmation">Confirmar contraseña:</label>
        <input type="password" id="contrasena_confirmation" name="contrasena_confirmation" autocomplete="new-password" @required($usuario === null)>
    </div>
@endif
