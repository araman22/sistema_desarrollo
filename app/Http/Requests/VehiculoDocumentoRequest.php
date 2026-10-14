<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehiculoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('vehiculo'));
    }

    public function rules(): array
    {
        return ['archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'mimetypes:application/pdf,image/jpeg,image/png', 'extensions:pdf,jpg,jpeg,png', 'max:5120']];
    }
}
