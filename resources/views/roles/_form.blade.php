{{-- Compartido por create y edit. $rol es null al crear. --}}
@php
    $esAdministrador = $rol !== null && App\Policies\RolPolicy::esAdministrador($rol);
    $puedeAsignar = auth()->user()->can('asignarPermisos', $rol ?? App\Models\Rol::class);
    $seleccionados = array_map('intval', (array) old('permisos', $permisosDelRol ?? []));
@endphp

<div>
    <label for="nombre">Nombre:</label>
    @if ($esAdministrador)
        {{-- El nombre del administrador es fijo: se envía el actual. --}}
        <input type="hidden" name="nombre" value="{{ $rol->nombre }}">
        <span>{{ $rol->nombre }} (no se puede cambiar)</span>
    @else
        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $rol?->nombre) }}" maxlength="100" required>
    @endif
    @error('nombre')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" maxlength="500" rows="3">{{ old('descripcion', $rol?->descripcion) }}</textarea>
    @error('descripcion')
        <p>{{ $message }}</p>
    @enderror
</div>

<div>
    @if ($esAdministrador)
        <input type="hidden" name="activo" value="1">
        <span>Activo (el rol administrador no se puede desactivar)</span>
    @else
        <input type="hidden" name="activo" value="0">
        <label>
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $rol?->activo ?? true))>
            Activo
        </label>
    @endif
    @error('activo')
        <p>{{ $message }}</p>
    @enderror
</div>

<fieldset>
    <legend>Permisos</legend>

    @if ($esAdministrador)
        <p>El rol administrador tiene todos los permisos y no se pueden modificar.</p>
    @elseif (! $puedeAsignar)
        <p>No tenés permiso para asignar permisos. Se muestran en solo lectura.</p>
    @else
        <input type="hidden" name="sincronizar_permisos" value="1">
    @endif

    @error('permisos')
        <p>{{ $message }}</p>
    @enderror
    @error('permisos.*')
        <p>{{ $message }}</p>
    @enderror

    @unless ($esAdministrador)
        @foreach ($permisosPorModulo as $modulo => $permisos)
            <fieldset>
                <legend>{{ $modulo }}</legend>
                @foreach ($permisos as $permiso)
                    <label title="{{ $permiso->descripcion }}">
                        @if ($puedeAsignar)
                            <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}" @checked(in_array($permiso->id, $seleccionados, true))>
                        @else
                            <input type="checkbox" disabled @checked(in_array($permiso->id, $seleccionados, true))>
                        @endif
                        {{ $permiso->accion }}
                    </label>
                @endforeach
            </fieldset>
        @endforeach
    @endunless
</fieldset>
