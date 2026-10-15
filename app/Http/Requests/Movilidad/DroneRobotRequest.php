<?php

namespace App\Http\Requests\Movilidad;

use App\Models\DroneRobot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DroneRobotRequest extends FormRequest
{
    public function authorize(): bool
    {
        $drone = $this->route('drone');

        return $drone ? $this->user()->can('update', $drone) : $this->user()->can('create', DroneRobot::class);
    }

    public function rules(): array
    {
        $drone = $this->route('drone');

        return [
            'tipo' => ['required', Rule::in(DroneRobot::TIPOS)],
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('drones_robots')->ignore($drone)],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100', Rule::unique('drones_robots')->ignore($drone)],
            'estado' => ['required', Rule::in(DroneRobot::ESTADOS)],
            'responsable_id' => ['nullable', 'integer', 'exists:policias,id'],
            'fecha_adquisicion' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'horas_uso' => ['required', 'numeric', 'decimal:0,2', 'min:'.($drone?->horas_uso ?? 0), 'max:99999999.99'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['horas_uso.min' => 'Las horas acumuladas no pueden disminuir.', 'codigo.unique' => 'El código ya existe.', 'numero_serie.unique' => 'El número de serie ya existe.'];
    }
}
