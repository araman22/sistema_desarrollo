<?php

namespace Database\Factories;

use App\Models\DroneRobot;
use App\Models\Mantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mantenimiento> */
class MantenimientoFactory extends Factory
{
    protected $model = Mantenimiento::class;

    public function definition(): array
    {
        return ['mantenible_id' => DroneRobot::factory(), 'mantenible_tipo' => (new DroneRobot)->getMorphClass(), 'tipo' => 'revision', 'descripcion' => 'Revisión de funcionamiento', 'fecha' => today(), 'estado' => 'programado', 'costo' => '0.00'];
    }
}
