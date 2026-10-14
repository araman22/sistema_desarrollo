<?php

namespace Database\Factories;

use App\Models\Bateria;
use App\Models\DroneRobot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bateria> */
class BateriaFactory extends Factory
{
    protected $model = Bateria::class;

    public function definition(): array
    {
        return ['dron_robot_id' => DroneRobot::factory(), 'codigo' => fake()->unique()->bothify('BAT-########'), 'numero_serie' => fake()->unique()->uuid(), 'capacidad' => '5000 mAh', 'estado' => 'disponible', 'ciclos' => 0, 'fecha_adquisicion' => today()->subMonth()];
    }
}
