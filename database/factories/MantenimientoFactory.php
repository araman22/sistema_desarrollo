<?php

namespace Database\Factories;

use App\Models\DroneRobot;
use App\Models\MantenimientoMovilidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MantenimientoMovilidad> */
class MantenimientoFactory extends Factory
{
    protected $model = MantenimientoMovilidad::class;

    public function definition(): array
    {
        return ['mantenible_id' => DroneRobot::factory(), 'mantenible_tipo' => (new DroneRobot)->getMorphClass(), 'tipo' => 'revision', 'descripcion' => 'Revisión de funcionamiento', 'fecha' => today(), 'estado' => 'programado', 'costo' => '0.00'];
    }
}
