<?php

namespace App\Http\Requests\Personal;

use App\Models\Licencia;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LicenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $licencia = $this->route('licencia');

        return $licencia ? $this->user()->can('update', $licencia) : $this->user()->can('create', Licencia::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['requiere_reemplazo' => $this->boolean('requiere_reemplazo')]);
    }

    public function rules(): array
    {
        $licencia = $this->route('licencia');
        $estadoActual = $licencia?->estado ?? 'pendiente';

        return [
            'policia_id' => ['required', 'exists:policias,id'],
            'tipo_licencia_id' => ['required', 'exists:tipo_licencias,id'],
            'anio_correspondiente' => ['required', 'integer', 'min:1980', 'max:'.(now()->year + 1)],
            'fecha_inicio' => ['required', 'date'],
            'cantidad_dias' => ['required', 'integer', 'min:1', 'max:365'],
            'fecha_regreso_efectivo' => ['nullable', 'date', 'after:fecha_inicio'],
            'estado' => ['required', Rule::in(array_keys(Licencia::ESTADOS)), function (string $attribute, mixed $value, Closure $fail) use ($estadoActual) {
                // Sin permiso de autorizar solo se puede cargar o mantener el estado actual.
                if ($value !== $estadoActual && ! $this->user()->can('autorizar', Licencia::class)) {
                    $fail('No tiene permiso para autorizar, rechazar o cancelar licencias.');
                }
            }],
            'motivo' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'requiere_reemplazo' => ['boolean'],
            'reemplazo_id' => ['nullable', 'required_if:requiere_reemplazo,true', 'exists:policias,id', 'different:policia_id'],
            'cobertura_desde' => ['nullable', 'required_if:requiere_reemplazo,true', 'date'],
            'cobertura_hasta' => ['nullable', 'required_if:requiere_reemplazo,true', 'date', 'after_or_equal:cobertura_desde'],
            'cobertura_observaciones' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reemplazo_id.required_if' => 'Indique quién cubrirá las funciones.',
            'reemplazo_id.different' => 'El reemplazo debe ser otro agente.',
            'cobertura_desde.required_if' => 'Indique desde cuándo se cubren las funciones.',
            'cobertura_hasta.required_if' => 'Indique hasta cuándo se cubren las funciones.',
        ];
    }
}
