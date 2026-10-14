<?php

namespace App\Http\Requests\Movilidad;

use App\Models\Mantenimiento;
use App\Support\RecursoMantenimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MantenimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('mantenimiento')
            ? $this->user()->can('movilidad.mantenimiento.update', RecursoMantenimiento::registro($this))
            : $this->user()->can('movilidad.mantenimiento.create', RecursoMantenimiento::desde($this));
    }

    public function rules(): array
    {
        $recurso = RecursoMantenimiento::desde($this);

        return [
            // El padre se determina exclusivamente por la URL y la relación, nunca por input.
            'mantenible_id' => ['prohibited'], 'mantenible_tipo' => ['prohibited'],
            'tipo' => ['required', Rule::in(Mantenimiento::TIPOS_MOVILIDAD)],
            'descripcion' => ['required', 'string', 'max:5000'],
            'fecha' => ['required', 'date_format:Y-m-d', Rule::when(in_array($this->input('estado'), ['realizado', 'en_curso'], true), 'before_or_equal:today'), Rule::when($recurso->fecha_adquisicion !== null, 'after_or_equal:'.($recurso->fecha_adquisicion?->toDateString() ?? '1900-01-01'))],
            'responsable_id' => ['nullable', 'integer', 'exists:policias,id'],
            'costo' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999999.99'],
            'estado' => ['required', Rule::in(Mantenimiento::ESTADOS_MOVILIDAD)],
            'resultado' => ['nullable', 'required_if:estado,realizado', 'string', 'max:5000'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
