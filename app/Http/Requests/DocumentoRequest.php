<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentoId = $this->route('documento')?->id;

        $rules = [
            'tipo_documento_id' => ['required', 'exists:tipo_documentos,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'numero' => ['required', 'integer', Rule::unique('documentos', 'numero')->where(fn ($query) => $query->where('tipo_documento_id', $this->input('tipo_documento_id')))->ignore($documentoId)],
            'descripcion' => ['required', 'string', 'max:255'],
            'archivo' => ['nullable', 'file', 'max:5120'],
        ];

        if ($this->isMethod('post') || $this->hasFile('archivo')) {
            $rules['archivo'][] = 'required';
        }

        return $rules;
    }
}
