<?php

namespace App\Services\Expedientes;

use App\Models\Expediente;
use App\Models\ExpedientePase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestorPases
{
    /**
     * Registra el envío. El origen es siempre la ubicación actual, y no se admite un
     * pase nuevo hasta que el anterior se reciba, para que el recorrido no tenga saltos.
     *
     * @param  array<string, mixed>  $datos
     */
    public static function enviar(Expediente $expediente, array $datos): ExpedientePase
    {
        return DB::transaction(function () use ($expediente, $datos) {
            $expediente = Expediente::query()->lockForUpdate()->findOrFail($expediente->id);

            if ($expediente->estado !== 'en_tramite') {
                throw ValidationException::withMessages(['destino' => 'Solo se pueden dar pases a expedientes en trámite.']);
            }
            if ($expediente->pases()->whereNull('fecha_recepcion')->exists()) {
                throw ValidationException::withMessages(['destino' => 'El expediente tiene un pase pendiente de recepción.']);
            }
            if (mb_strtolower(trim($datos['destino'])) === mb_strtolower(trim($expediente->ubicacion_actual))) {
                throw ValidationException::withMessages(['destino' => 'El destino debe ser distinto de la ubicación actual.']);
            }
            $fechaEnvio = Carbon::parse($datos['fecha_envio']);
            $ultimaRecepcion = $expediente->pases()->max('fecha_recepcion');
            if ($ultimaRecepcion && $fechaEnvio->lt(Carbon::parse($ultimaRecepcion))) {
                throw ValidationException::withMessages(['fecha_envio' => 'La fecha de envío no puede ser anterior a la última recepción ('.Carbon::parse($ultimaRecepcion)->format('d/m/Y H:i').').']);
            }

            return $expediente->pases()->create([
                'origen' => $expediente->ubicacion_actual,
                'destino' => trim($datos['destino']),
                'fecha_envio' => $fechaEnvio,
                'enviado_por_id' => $datos['enviado_por_id'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'usuario_envio_id' => Auth::id(),
            ]);
        });
    }

    /**
     * Registra la recepción: el expediente pasa a estar en el destino del pase.
     *
     * @param  array<string, mixed>  $datos
     */
    public static function recibir(ExpedientePase $pase, array $datos): ExpedientePase
    {
        return DB::transaction(function () use ($pase, $datos) {
            $expediente = Expediente::query()->lockForUpdate()->findOrFail($pase->expediente_id);
            $pase = ExpedientePase::query()->lockForUpdate()->findOrFail($pase->id);

            if ($pase->fecha_recepcion) {
                throw ValidationException::withMessages(['fecha_recepcion' => 'El pase ya fue recibido.']);
            }
            $fechaRecepcion = Carbon::parse($datos['fecha_recepcion']);
            if ($fechaRecepcion->lt($pase->fecha_envio)) {
                throw ValidationException::withMessages(['fecha_recepcion' => 'La recepción no puede ser anterior al envío ('.$pase->fecha_envio->format('d/m/Y H:i').').']);
            }

            $pase->update([
                'fecha_recepcion' => $fechaRecepcion,
                'recibido_por_id' => $datos['recibido_por_id'] ?? null,
                'observaciones_recepcion' => $datos['observaciones_recepcion'] ?? null,
                'usuario_recepcion_id' => Auth::id(),
            ]);
            $expediente->update(array_filter([
                'ubicacion_actual' => $pase->destino,
                'responsable_id' => $datos['recibido_por_id'] ?? null,
            ]));

            return $pase;
        });
    }
}
