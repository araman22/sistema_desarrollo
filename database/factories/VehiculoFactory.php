<?php

namespace Database\Factories;

use App\Models\TipoVehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    public function definition(): array
    {
        return ['tipo_vehiculo_id' => TipoVehiculo::firstOrCreate(['nombre' => 'Automóvil'])->id, 'dominio' => fake()->unique()->bothify('??###??'), 'estado' => 'disponible', 'kilometraje' => 1000, 'anio' => 2020];
    }
}
