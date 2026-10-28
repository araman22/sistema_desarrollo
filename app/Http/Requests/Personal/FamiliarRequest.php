<?php

namespace App\Http\Requests\Personal;

use App\Models\Familiar;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamiliarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('policia'));
    }

    public function rules(): array
    {
        $policia = $this->route('policia');

        return [
            'parentesco' => ['required', Rule::in(array_keys(Familiar::PARENTESCOS)), function (string $attribute, mixed $value, Closure $fail) use ($policia) {
                if ($value === 'conyuge' && $policia->familiares()->where('parentesco', 'conyuge')->exists()) {
                    $fail('El agente ya tiene un cónyuge registrado.');
                }
            }],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['nullable', 'string', 'max:20', Rule::unique('familiares', 'dni')->where('policia_id', $policia->id)],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}
