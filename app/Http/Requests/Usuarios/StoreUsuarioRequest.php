<?php

namespace App\Http\Requests\Usuarios;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Usuario::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('usuarios', 'nombre_usuario')],
            'correo_electronico' => ['nullable', 'email', 'max:150', Rule::unique('usuarios', 'correo_electronico')],
            'contrasena' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'rol_id' => ['required', Rule::exists('roles', 'id')->where('activo', true)],
            'policia_id' => ['nullable', Rule::exists('policias', 'id'), Rule::unique('usuarios', 'policia_id')],
            'activo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no puede superar los :max caracteres.',
            'alpha_dash' => 'El campo :attribute solo puede tener letras, números, guiones y guiones bajos.',
            'unique' => 'Ya existe un usuario con ese :attribute.',
            'email' => 'El campo :attribute debe ser un correo electrónico válido.',
            'exists' => 'El :attribute seleccionado no es válido.',
            'boolean' => 'El campo :attribute no es válido.',
            'confirmed' => 'La confirmación de la contraseña no coincide.',
            'contrasena.min' => 'La contraseña debe tener al menos :min caracteres.',
            'contrasena.password.letters' => 'La contraseña debe tener al menos una letra.',
            'contrasena.password.numbers' => 'La contraseña debe tener al menos un número.',
            'policia_id.unique' => 'Ese policía ya tiene un usuario asignado.',
            'rol_id.exists' => 'El rol seleccionado no existe o está inactivo.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre_usuario' => 'nombre de usuario',
            'correo_electronico' => 'correo electrónico',
            'contrasena' => 'contraseña',
            'rol_id' => 'rol',
            'policia_id' => 'policía',
            'activo' => 'activo',
        ];
    }
}
