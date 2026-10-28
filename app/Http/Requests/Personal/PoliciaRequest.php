<?php

namespace App\Http\Requests\Personal;

use App\Models\Persona;
use App\Models\Policia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PoliciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $policia = $this->route('policia');

        return $policia ? $this->user()->can('update', $policia) : $this->user()->can('create', Policia::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['funciones_adicionales' => array_values(array_filter((array) $this->input('funciones_adicionales', [])))]);
    }

    public function rules(): array
    {
        $policia = $this->route('policia');

        return [
            'dni' => ['required', 'string', 'max:20', Rule::unique('personas', 'dni')->ignore($policia?->persona_id)],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'estado_civil' => ['nullable', Rule::in(array_keys(Persona::ESTADOS_CIVILES))],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'jerarquia_id' => ['required', 'exists:jerarquias,id'],
            'numero_legajo' => ['required', 'string', 'max:50', Rule::unique('policias', 'numero_legajo')->ignore($policia?->id)],
            'funcion_id' => ['required', 'exists:funciones,id'],
            'funciones_adicionales' => ['array'],
            'funciones_adicionales.*' => ['integer', 'distinct', 'exists:funciones,id', 'different:funcion_id'],
            'oficina_id' => ['nullable', 'exists:oficinas,id'],
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:today'],
            'estado' => ['required', Rule::in(['activo', 'inactivo'])],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['funciones_adicionales.*.different' => 'Una función adicional no puede ser la función principal.'];
    }

    /**
     * @return array<string, mixed>
     */
    public function datosPersona(): array
    {
        return $this->safe()->only(['dni', 'nombre', 'apellido', 'fecha_nacimiento', 'sexo', 'estado_civil', 'direccion', 'telefono', 'correo_electronico']);
    }

    /**
     * @return array<string, mixed>
     */
    public function datosPolicia(): array
    {
        return $this->safe()->only(['jerarquia_id', 'numero_legajo', 'funcion_id', 'oficina_id', 'fecha_ingreso', 'estado', 'observaciones']);
    }
}
