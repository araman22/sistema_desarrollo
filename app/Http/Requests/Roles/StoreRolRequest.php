<?php

namespace App\Http\Requests\Roles;

use App\Models\Rol;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Rol::class);
    }

    /**
     * El formulario manda "sincronizar_permisos" cuando muestra los checkboxes:
     * así, si se destildan todos, llega permisos = [] y no "sin cambios".
     */
    protected function prepareForValidation(): void
    {
        if ($this->boolean('sincronizar_permisos') && ! $this->has('permisos')) {
            $this->merge(['permisos' => []]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('roles', 'nombre')],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'activo' => ['boolean'],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'distinct', Rule::exists('permisos', 'id')],
        ];
    }

    /**
     * Enviar permisos (aunque sea una lista vacía) requiere poder asignarlos.
     * Va en after() porque las reglas comunes no se evalúan con un array vacío.
     *
     * @return array<int, Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->has('permisos')) {
                    return;
                }

                $respuesta = Gate::forUser($this->user())->inspect('asignarPermisos', $this->rolDestino() ?? Rol::class);

                if ($respuesta->denied()) {
                    $validator->errors()->add('permisos', $respuesta->message());
                }
            },
        ];
    }

    /**
     * Rol sobre el que se asignan permisos: ninguno al crear.
     */
    protected function rolDestino(): ?Rol
    {
        return null;
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
            'unique' => 'Ya existe un rol con ese :attribute.',
            'boolean' => 'El campo :attribute no es válido.',
            'array' => 'El campo :attribute no es válido.',
            'permisos.*.integer' => 'Uno de los permisos seleccionados no es válido.',
            'permisos.*.distinct' => 'Hay permisos repetidos.',
            'permisos.*.exists' => 'Uno de los permisos seleccionados no existe.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'descripcion' => 'descripción',
            'activo' => 'activo',
            'permisos' => 'permisos',
        ];
    }
}
