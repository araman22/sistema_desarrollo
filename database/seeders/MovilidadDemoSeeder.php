<?php

namespace Database\Seeders;

use App\Models\Bateria;
use App\Models\DroneRobot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Datos optativos de demostración; nunca concede permisos ni crea usuarios. */
class MovilidadDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (['DR-DEMO-01' => 'dron', 'RB-DEMO-01' => 'robot'] as $codigo => $tipo) {
                $drone = DroneRobot::firstOrCreate(['codigo' => $codigo], ['tipo' => $tipo, 'nombre' => ucfirst($tipo).' de demostración', 'estado' => 'operativo', 'horas_uso' => 0, 'fecha_adquisicion' => today()->subYear()]);
                $bateria = Bateria::firstOrCreate(['codigo' => 'BAT-'.$codigo], ['dron_robot_id' => $drone->id, 'capacidad' => '5000 mAh', 'ciclos' => 0, 'estado' => 'disponible', 'fecha_adquisicion' => today()->subMonth()]);
                $drone->mantenimientos()->firstOrCreate(['descripcion' => 'Revisión inicial de demostración'], ['tipo' => 'revision', 'fecha' => today()->toDateString(), 'estado' => 'programado']);
                $bateria->mantenimientos()->firstOrCreate(['descripcion' => 'Revisión de batería de demostración'], ['tipo' => 'revision', 'fecha' => today()->toDateString(), 'estado' => 'programado']);
            }
        });
    }
}
