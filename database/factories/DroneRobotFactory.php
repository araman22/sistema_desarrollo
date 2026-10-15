<?php

namespace Database\Factories;

use App\Models\DroneRobot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DroneRobot> */
class DroneRobotFactory extends Factory
{
    protected $model = DroneRobot::class;

    public function definition(): array
    {
        return ['tipo' => fake()->randomElement(DroneRobot::TIPOS), 'nombre' => 'Recurso '.fake()->word(), 'codigo' => fake()->unique()->bothify('DR-########'), 'numero_serie' => fake()->unique()->uuid(), 'estado' => 'operativo', 'horas_uso' => 0, 'fecha_adquisicion' => today()->subYear(), 'responsable_id' => null];
    }
}
