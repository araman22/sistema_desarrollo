<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovilidadAuditoria
{
    // Escribe en la auditoría general, pero participa de la transacción: si falla, no se confirma el cambio.
    public static function registrar(string $accion, Model $modelo, ?array $anterior = null): void
    {
        DB::table('auditorias')->insert([
            'usuario_id' => auth()->id(),
            'accion' => $accion,
            'tabla_afectada' => $modelo->getTable(),
            'registro_id' => $modelo->getKey(),
            'valor_anterior' => $anterior === null ? null : json_encode($anterior, JSON_THROW_ON_ERROR),
            'valor_nuevo' => $accion === 'eliminar' ? null : json_encode($modelo->getAttributes(), JSON_THROW_ON_ERROR),
            'direccion_ip' => app()->runningInConsole() ? null : request()->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
