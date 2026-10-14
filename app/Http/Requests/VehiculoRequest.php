<?php

namespace App\Http\Requests;

use App\Models\Vehiculo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $v = $this->route('vehiculo');

        return $v ? $this->user()->can('update', $v) : $this->user()->can('create', Vehiculo::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['dominio' => mb_strtoupper(trim((string) $this->dominio))]);
    }

    public function rules(): array
    {
        return [
            'tipo_vehiculo_id' => ['required', 'integer', 'exists:tipo_vehiculos,id'],
            'dominio' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/', Rule::unique('vehiculos', 'dominio')->ignore($this->route('vehiculo'))],
            'anio' => ['nullable', 'integer', 'between:1900,'.(now()->year + 1)],
            'numero_chasis' => ['nullable', 'string', 'max:100'], 'numero_motor' => ['nullable', 'string', 'max:100'],
            'kilometraje' => ['required', 'integer', 'between:0,4294967295'],
            'responsable_id' => ['nullable', 'integer', Rule::exists('policias', 'id')->where('estado', 'activo')],
            'estado' => ['required', Rule::in(['disponible', 'en_uso', 'mantenimiento', 'fuera_servicio', 'baja'])],
            'compania_seguro' => ['nullable', 'string', 'max:150', 'required_with:numero_poliza,vencimiento_seguro'],
            'numero_poliza' => ['nullable', 'string', 'max:100', 'required_with:compania_seguro,vencimiento_seguro'],
            'vencimiento_seguro' => ['nullable', 'date_format:Y-m-d', 'required_with:compania_seguro,numero_poliza'],
            'ultima_revision' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'proxima_revision' => ['nullable', 'date_format:Y-m-d', Rule::when((bool) $this->ultima_revision, 'after_or_equal:ultima_revision')],
            'fecha_adquisicion' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
