<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tipoDocumentoId = $this->route('tipoDocumento')?->id;

        return [
            'nombre' => ['required', 'string', 'max:100', Rule::unique('tipo_documentos', 'nombre')->ignore($tipoDocumentoId)],
        ];
    }
}
