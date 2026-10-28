<?php

namespace App\Http\Requests\Personal;

use App\Models\NovedadRevista;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NovedadRevistaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('policia'));
    }

    public function rules(): array
    {
        // La situación "licencia" se genera desde el módulo de licencias.
        return [
            'situacion' => ['required', Rule::in(array_diff(array_keys(NovedadRevista::SITUACIONES), ['licencia']))],
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
