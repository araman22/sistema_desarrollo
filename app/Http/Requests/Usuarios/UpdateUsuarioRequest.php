<?php

namespace App\Http\Requests\Usuarios;

use App\Models\Usuario;
use App\Policies\UsuarioPolicy;
use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Hereda mensajes y nombres de atributos de StoreUsuarioRequest.
 */
class UpdateUsuarioRequest extends StoreUsuarioRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->usuarioEditado());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $usuario = $this->usuarioEditado();
        $esPropio = $this->user()->is($usuario);

        return [
            'nombre_usuario' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('usuarios', 'nombre_usuario')->ignore($usuario)],
            'correo_electronico' => ['nullable', 'email', 'max:150', Rule::unique('usuarios', 'correo_electronico')->ignore($usuario)],
            'contrasena' => [
                'nullable',
                'confirmed',
                Password::min(8)->letters()->numbers(),
                function (string $attribute, mixed $value, Closure $fail) use ($usuario) {
                    if (filled($value) && $this->user()->cannot('restablecerContrasena', $usuario)) {
                        $fail('No tenés permiso para cambiar la contraseña de este usuario.');
                    }
                },
            ],
            'rol_id' => [
                'required',
                Rule::exists('roles', 'id')->where('activo', true),
                function (string $attribute, mixed $value, Closure $fail) use ($usuario, $esPropio) {
                    if ((int) $value === (int) $usuario->rol_id) {
                        return;
                    }

                    if ($esPropio) {
                        $fail('No podés cambiar tu propio rol.');
                    } elseif (UsuarioPolicy::esUltimoAdministradorActivo($usuario)) {
                        $fail('No se puede cambiar el rol del último administrador activo del sistema.');
                    }
                },
            ],
            'policia_id' => ['nullable', Rule::exists('policias', 'id'), Rule::unique('usuarios', 'policia_id')->ignore($usuario)],
            'activo' => [
                'boolean',
                function (string $attribute, mixed $value, Closure $fail) use ($usuario, $esPropio) {
                    if ((bool) $value === (bool) $usuario->activo) {
                        return;
                    }

                    if ($esPropio) {
                        $fail('No podés activar ni desactivar tu propio usuario.');
                    } elseif (UsuarioPolicy::esUltimoAdministradorActivo($usuario)) {
                        $fail('No se puede desactivar al último administrador activo del sistema.');
                    }
                },
            ],
        ];
    }

    private function usuarioEditado(): Usuario
    {
        return $this->route('usuario');
    }
}
