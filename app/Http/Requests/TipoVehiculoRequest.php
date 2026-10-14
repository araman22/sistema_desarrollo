<?php

namespace App\Http\Requests;

use App\Models\TipoVehiculo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $t = $this->route('tipo');

        return $t ? $this->user()->can('update', $t) : $this->user()->can('create', TipoVehiculo::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nombre' => trim((string) $this->nombre)]);
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:100', Rule::unique('tipo_vehiculos', 'nombre')->ignore($this->route('tipo'))]];
    }
}
