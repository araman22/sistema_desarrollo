<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipamientoTecnologicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $equipamientoId = $this->route('equipamientoTecnologico')?->id;

        return [
            'codigo' => ['required', 'string', 'max:50', Rule::unique('equipamientos_tecnologicos', 'codigo')->ignore($equipamientoId)],
            'tipo' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:150'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'numero_serie' => ['nullable', 'string', 'max:100', Rule::unique('equipamientos_tecnologicos', 'numero_serie')->ignore($equipamientoId)],
            'procesador' => ['nullable', 'string', 'max:150'],
            'memoria_ram' => ['nullable', 'string', 'max:50'],
            'almacenamiento' => ['nullable', 'string', 'max:100'],
            'sistema_operativo' => ['nullable', 'string', 'max:100'],
            'direccion_ip' => ['nullable', 'ip'],
            'direccion_mac' => ['nullable', 'regex:/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/'],
            'oficina_id' => ['nullable', 'exists:oficinas,id'],
            'estado' => ['required', Rule::in(['activo', 'inactivo', 'en_mantenimiento', 'baja'])],
            'fecha_adquisicion' => ['nullable', 'date'],
            'codigo_qr' => ['nullable', 'string', 'max:150', Rule::unique('equipamientos_tecnologicos', 'codigo_qr')->ignore($equipamientoId)],
            'observaciones' => ['nullable', 'string'],
        ];
    }
}
