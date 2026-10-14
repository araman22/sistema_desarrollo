<?php

namespace App\Services\Movilidad;

use App\Models\Bateria;
use App\Models\DroneRobot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestionRecursos
{
    public function drone(array $datos, ?DroneRobot $drone = null): DroneRobot
    {
        return DB::transaction(function () use ($datos, $drone) {
            $drone = $drone ? DroneRobot::lockForUpdate()->findOrFail($drone->id) : new DroneRobot;
            if ($drone->exists && $datos['horas_uso'] < $drone->horas_uso) {
                throw ValidationException::withMessages(['horas_uso' => 'Las horas acumuladas no pueden disminuir.']);
            }
            $drone->fill($datos)->save();

            return $drone;
        }, 3);
    }

    public function bateria(array $datos, ?Bateria $bateria = null): Bateria
    {
        return DB::transaction(function () use ($datos, $bateria) {
            $drone = DroneRobot::lockForUpdate()->findOrFail($datos['dron_robot_id']);
            if ($drone->estado === 'baja') {
                throw ValidationException::withMessages(['dron_robot_id' => 'No se puede asignar una batería a un recurso dado de baja.']);
            }
            $bateria = $bateria ? Bateria::lockForUpdate()->findOrFail($bateria->id) : new Bateria;
            if ($bateria->exists && $datos['ciclos'] < $bateria->ciclos) {
                throw ValidationException::withMessages(['ciclos' => 'Los ciclos acumulados no pueden disminuir.']);
            }
            if ($bateria->exists && $bateria->ultima_revision && ! empty($datos['ultima_revision']) && $datos['ultima_revision'] < $bateria->ultima_revision->toDateString()) {
                throw ValidationException::withMessages(['ultima_revision' => 'La última revisión no puede retroceder.']);
            }
            if ($bateria->exists && $bateria->ultima_revision && empty($datos['ultima_revision'])) {
                throw ValidationException::withMessages(['ultima_revision' => 'No se puede quitar una revisión registrada.']);
            }
            $bateria->fill($datos);
            if ($bateria->ultima_revision && $bateria->fecha_adquisicion && $bateria->ultima_revision->lessThan($bateria->fecha_adquisicion)) {
                throw ValidationException::withMessages(['ultima_revision' => 'La revisión no puede ser anterior a la adquisición.']);
            }
            $bateria->save();

            return $bateria;
        }, 3);
    }

    public function eliminar(DroneRobot|Bateria $recurso): void
    {
        DB::transaction(function () use ($recurso) {
            $recurso = $recurso::query()->lockForUpdate()->findOrFail($recurso->id);
            $recurso->delete();
        }, 3);
    }
}
