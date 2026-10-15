<?php

namespace App\Http\Requests\Roles;

use App\Models\Rol;
use App\Policies\RolPolicy;
use Closure;
use Illuminate\Validation\Rule;

/**
 * Hereda mensajes, atributos y el control de asignación de permisos de
 * StoreRolRequest.
 */
class UpdateRolRequest extends StoreRolRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->rolDestino());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rol = $this->rolDestino();
        $esAdministrador = RolPolicy::esAdministrador($rol);

        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'nombre')->ignore($rol),
                function (string $attribute, mixed $value, Closure $fail) use ($rol, $esAdministrador) {
                    if ($esAdministrador && $value !== $rol->nombre) {
                        $fail('El nombre del rol administrador no se puede cambiar.');
                    }
                },
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => [
                'boolean',
                function (string $attribute, mixed $value, Closure $fail) use ($esAdministrador) {
                    if ($esAdministrador && ! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                        $fail('El rol administrador no se puede desactivar.');
                    }
                },
            ],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'distinct', Rule::exists('permisos', 'id')],
        ];
    }

    protected function rolDestino(): Rol
    {
        return $this->route('rol');
    }
}
