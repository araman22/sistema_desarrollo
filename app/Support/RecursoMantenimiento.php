<?php

namespace App\Support;

use App\Models\Bateria;
use App\Models\DroneRobot;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;

class RecursoMantenimiento
{
    public static function desde(Request $request): DroneRobot|Bateria
    {
        $recurso = $request->route('drone') ?? $request->route('bateria');
        abort_unless($recurso instanceof DroneRobot || $recurso instanceof Bateria, 404);

        return $recurso;
    }

    public static function registro(Request $request): Mantenimiento
    {
        return self::desde($request)->mantenimientos()->findOrFail($request->route('mantenimiento'));
    }

    public static function prefijo(DroneRobot|Bateria $recurso): string
    {
        return $recurso instanceof DroneRobot ? 'movilidad.drones.mantenimientos.' : 'movilidad.baterias.mantenimientos.';
    }
}
