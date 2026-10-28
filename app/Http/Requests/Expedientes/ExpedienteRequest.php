<?php

namespace App\Http\Requests\Expedientes;

use App\Models\Expediente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpedienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expediente = $this->route('expediente');

        return $expediente ? $this->user()->can('update', $expediente) : $this->user()->can('create', Expediente::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'numero' => trim((string) $this->input('numero')),
            'documentos' => array_values(array_filter((array) $this->input('documentos', []))),
        ]);
    }

    public function rules(): array
    {
        $expediente = $this->route('expediente');

        return [
            'bibliorato_id' => ['required', Rule::exists('biblioratos', 'id')->where(fn ($q) => $expediente ? $q : $q->where('activo', true))],
            'numero' => ['required', 'string', 'max:50', Rule::unique('expedientes', 'numero')->ignore($expediente?->id)],
            'asunto' => ['required', 'string', 'max:255'],
            'origen' => ['required', 'string', 'max:150'],
            'destino' => ['required', 'string', 'max:150'],
            'fecha_emision' => ['required', 'date', 'before_or_equal:today'],
            'fecha_recepcion' => ['nullable', 'date', 'after_or_equal:fecha_emision', 'before_or_equal:today'],
            'estado' => ['required', Rule::in(array_keys(Expediente::ESTADOS))],
            'responsable_id' => ['nullable', 'exists:policias,id'],
            // La ubicación se mueve con los pases; solo se carga al crear.
            'ubicacion_actual' => [$expediente ? 'prohibited' : 'required', 'string', 'max:150'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'documentos' => ['array'],
            'documentos.*' => ['integer', 'distinct', 'exists:documentos,id'],
        ];
    }

    public function messages(): array
    {
        return ['ubicacion_actual.prohibited' => 'La ubicación actual se modifica registrando un pase.'];
    }
}
