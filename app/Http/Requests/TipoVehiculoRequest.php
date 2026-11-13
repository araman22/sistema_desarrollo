<?php

namespace App\Http\Requests;

use App\Models\TipoVehiculo;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class TipoVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $t = $this->route('tipo');

        return $t ? $this->user()->can('update', $t) : $this->user()->can('create', TipoVehiculo::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nombre' => TipoVehiculo::limpiar($this->input('nombre'))]);
    }

    public function rules(): array
    {
        return ['nombre' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, Closure $fail) {
            if ($existente = TipoVehiculo::equivalenteA($value, $this->route('tipo')?->id)) {
                $fail(self::mensajeDuplicado($existente));
            }
        }]];
    }

    public static function mensajeDuplicado(TipoVehiculo $existente): string
    {
        return "Ya existe el tipo de vehículo «{$existente->nombre}».";
    }
}
