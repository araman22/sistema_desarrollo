<?php

namespace App\Http\Requests\Movilidad;

use App\Models\Bateria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bateria = $this->route('bateria');

        return $bateria ? $this->user()->can('update', $bateria) : $this->user()->can('create', Bateria::class);
    }

    public function rules(): array
    {
        $bateria = $this->route('bateria');

        return [
            'dron_robot_id' => ['required', 'integer', 'exists:drones_robots,id'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('baterias')->ignore($bateria)],
            'marca' => ['nullable', 'string', 'max:100'], 'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100', Rule::unique('baterias')->ignore($bateria)],
            'capacidad' => ['nullable', 'string', 'max:50'],
            'ciclos' => ['required', 'integer', 'min:'.($bateria?->ciclos ?? 0), 'max:4294967295'],
            'estado' => ['required', Rule::in(Bateria::ESTADOS)],
            'fecha_adquisicion' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'ultima_revision' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today', Rule::when($this->filled('fecha_adquisicion'), 'after_or_equal:fecha_adquisicion')],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['ciclos.min' => 'Los ciclos acumulados no pueden disminuir.', 'codigo.unique' => 'El código ya existe.', 'numero_serie.unique' => 'El número de serie ya existe.'];
    }
}
