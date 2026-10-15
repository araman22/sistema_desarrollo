<?php

namespace App\Http\Controllers\Movilidad;

use App\Http\Requests\Movilidad\DroneRobotRequest;
use App\Models\DroneRobot;
use App\Services\Movilidad\GestionRecursos;

class DroneRobotController extends RecursoController
{
    protected function modelo(): string
    {
        return DroneRobot::class;
    }

    protected function modulo(): string
    {
        return 'drones';
    }

    protected function campos(): array
    {
        return ['tipo' => 'Tipo', 'nombre' => 'Nombre', 'codigo' => 'Código', 'marca' => 'Marca', 'modelo' => 'Modelo', 'numero_serie' => 'Número de serie', 'estado' => 'Estado', 'responsable_id' => 'Responsable', 'fecha_adquisicion' => 'Fecha de adquisición', 'horas_uso' => 'Horas de uso / vuelo', 'observaciones' => 'Observaciones'];
    }

    public function store(DroneRobotRequest $request, GestionRecursos $gestion)
    {
        $drone = $gestion->drone($request->validated());

        return redirect()->route('movilidad.drones.show', $drone)->with('status', 'Recurso registrado.');
    }

    public function update(DroneRobotRequest $request, DroneRobot $drone, GestionRecursos $gestion)
    {
        $gestion->drone($request->validated(), $drone);

        return redirect()->route('movilidad.drones.show', $drone)->with('status', 'Recurso actualizado.');
    }
}
