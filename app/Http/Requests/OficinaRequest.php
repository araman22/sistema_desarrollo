<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OficinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $oficinaId = $this->route('oficina')?->id;

        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('oficinas', 'nombre')->ignore($oficinaId)],
            'descripcion' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'responsable_id' => ['nullable', 'exists:policias,id'],
            'estado' => ['required', Rule::in(['activa', 'inactiva', 'cerrada'])],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
