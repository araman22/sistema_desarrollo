<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inventarioId = $this->route('inventario')?->id;

        return [
            'codigo' => ['required', 'string', 'max:50', Rule::unique('inventarios', 'codigo')->ignore($inventarioId)],
            'descripcion' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100', Rule::unique('inventarios', 'numero_serie')->ignore($inventarioId)],
            'estado' => ['required', Rule::in(['disponible', 'en_uso', 'en_mantenimiento', 'baja', 'perdido'])],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'oficina_id' => ['nullable', 'exists:oficinas,id'],
            'responsable_id' => ['nullable', 'exists:policias,id'],
            'fecha_adquisicion' => ['nullable', 'date'],
            'valor_adquisicion' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string'],
            'codigo_qr' => ['nullable', 'string', 'max:150', Rule::unique('inventarios', 'codigo_qr')->ignore($inventarioId)],
        ];
    }
}
