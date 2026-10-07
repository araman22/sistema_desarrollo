<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CajaChicaMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $rules = [
            'tipo' => ['required', 'in:ingreso,egreso'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha' => ['required', 'date'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'ticket' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];

        if ($this->input('tipo') === 'ingreso') {
            $rules['origen'] = ['required', 'string', 'max:255'];
            $rules['destino'] = ['nullable', 'string', 'max:255'];
        }

        if ($this->input('tipo') === 'egreso') {
            $rules['destino'] = ['required', 'string', 'max:255'];
            $rules['origen'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'tipo' => 'tipo de movimiento',
            'monto' => 'monto',
            'fecha' => 'fecha',
            'origen' => 'origen del dinero',
            'destino' => 'destino del dinero',
            'descripcion' => 'descripción',
            'ticket' => 'ticket de compra',
        ];
    }
}
