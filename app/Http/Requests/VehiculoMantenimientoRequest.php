<?php

namespace App\Http\Requests;

use App\Models\VehiculoMantenimiento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehiculoMantenimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $m = $this->route('mantenimiento');

        return $this->user()->can('view', $this->route('vehiculo')) && ($m ? $this->user()->can('update', $m) : $this->user()->can('create', VehiculoMantenimiento::class));
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['preventivo', 'correctivo', 'revision'])],
            'descripcion' => ['required', 'string', 'max:5000'],
            'fecha' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'kilometraje' => ['required', 'integer', 'between:0,4294967295'],
            'costo' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'proveedor' => ['nullable', 'string', 'max:200'],
            'responsable_id' => ['nullable', 'integer', Rule::exists('policias', 'id')->where('estado', 'activo')],
            'proxima_fecha' => ['nullable', 'date_format:Y-m-d', 'after:fecha'],
            'proximo_kilometraje' => ['nullable', 'integer', 'max:4294967295', 'gt:kilometraje'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
