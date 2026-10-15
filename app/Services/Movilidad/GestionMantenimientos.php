<?php

namespace App\Services\Movilidad;

use App\Models\Bateria;
use App\Models\DroneRobot;
use App\Models\Mantenimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestionMantenimientos
{
    public function guardar(DroneRobot|Bateria $recurso, array $datos, ?Mantenimiento $mantenimiento = null): Mantenimiento
    {
        return DB::transaction(function () use ($recurso, $datos, $mantenimiento) {
            $recurso = $recurso::query()->lockForUpdate()->findOrFail($recurso->id);
            $mantenimiento = $mantenimiento ? $recurso->mantenimientos()->lockForUpdate()->findOrFail($mantenimiento->id) : $recurso->mantenimientos()->make();
            // Un trabajo ya realizado conserva su fecha, tipo y resultado originales.
            if ($mantenimiento->exists && in_array($mantenimiento->estado, ['realizado', 'cancelado'], true)) {
                throw ValidationException::withMessages(['mantenimiento' => 'Un mantenimiento realizado o cancelado conserva su historial y no puede modificarse.']);
            }
            if ($mantenimiento->exists && $mantenimiento->estado === 'en_curso' && $datos['estado'] === 'programado') {
                throw ValidationException::withMessages(['estado' => 'Un trabajo iniciado no puede volver a programado.']);
            }
            if ($recurso->estado === 'baja') {
                throw ValidationException::withMessages(['recurso' => 'No se puede registrar mantenimiento en un recurso dado de baja.']);
            }
            if ($recurso->fecha_adquisicion && $datos['fecha'] < $recurso->fecha_adquisicion->toDateString()) {
                throw ValidationException::withMessages(['fecha' => 'El mantenimiento no puede ser anterior a la adquisición.']);
            }
            $mantenimiento->fill($datos)->save();
            if ($recurso instanceof Bateria && $mantenimiento->estado === 'realizado'
                && (! $recurso->ultima_revision || $mantenimiento->fecha->greaterThan($recurso->ultima_revision))) {
                $recurso->update(['ultima_revision' => $mantenimiento->fecha->toDateString()]);
            }

            return $mantenimiento;
        }, 3);
    }

    public function eliminar(DroneRobot|Bateria $recurso, Mantenimiento $mantenimiento): void
    {
        DB::transaction(function () use ($recurso, $mantenimiento) {
            $recurso = $recurso::query()->lockForUpdate()->findOrFail($recurso->id);
            $mantenimiento = $recurso->mantenimientos()->lockForUpdate()->findOrFail($mantenimiento->id);
            if ($mantenimiento->estado !== 'programado') {
                throw ValidationException::withMessages(['mantenimiento' => 'Solo pueden eliminarse trabajos programados.']);
            }
            $mantenimiento->delete();
        }, 3);
    }
}
