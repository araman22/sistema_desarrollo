<?php

namespace Database\Seeders;

use App\Models\Bateria;
use App\Models\DroneRobot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Datos optativos de demostración; nunca concede permisos ni crea usuarios. */
class MovilidadDemoSeeder extends Seeder
{
    /**
     * Codigo -> [tipo, marca, modelo, estado].
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    private const EQUIPOS = [
        'DR-DEMO-01' => ['dron', 'DJI', 'Matrice 300', 'operativo'],
        'RB-DEMO-01' => ['robot', 'Telerob', 'tEODor', 'operativo'],
        'DR-DEMO-02' => ['dron', 'DJI', 'Mavic 3 Enterprise', 'operativo'],
        'DR-DEMO-03' => ['dron', 'Autel', 'EVO II Dual', 'en_mantenimiento'],
        'RB-DEMO-02' => ['robot', 'Boston Dynamics', 'Spot', 'fuera_servicio'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::EQUIPOS as $codigo => [$tipo, $marca, $modelo, $estado]) {
                $drone = DroneRobot::firstOrCreate(['codigo' => $codigo], ['tipo' => $tipo, 'nombre' => ucfirst($tipo).' de demostración', 'marca' => $marca, 'modelo' => $modelo, 'numero_serie' => 'NS-'.$codigo, 'estado' => $estado, 'horas_uso' => 0, 'fecha_adquisicion' => today()->subYear()]);
                $bateria = Bateria::firstOrCreate(['codigo' => 'BAT-'.$codigo], ['dron_robot_id' => $drone->id, 'numero_serie' => 'NS-BAT-'.$codigo, 'capacidad' => '5000 mAh', 'ciclos' => 0, 'estado' => 'disponible', 'fecha_adquisicion' => today()->subMonth()]);
                $drone->mantenimientos()->firstOrCreate(['descripcion' => 'Revisión inicial de demostración'], ['tipo' => 'revision', 'fecha' => today()->toDateString(), 'estado' => 'programado']);
                $bateria->mantenimientos()->firstOrCreate(['descripcion' => 'Revisión de batería de demostración'], ['tipo' => 'revision', 'fecha' => today()->toDateString(), 'estado' => 'programado']);
            }
        });
    }
}
