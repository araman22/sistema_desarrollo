<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovilidadAuditoria
{
    // La auditoría participa de la transacción: si falla, no se confirma el cambio.
    public static function registrar(string $accion, Model $modelo, ?array $anterior = null): void
    {
        DB::table('movilidad_auditorias')->insert([
            'usuario_id' => auth()->id(), 'accion' => $accion, 'tabla' => $modelo->getTable(), 'registro_id' => $modelo->getKey(),
            'anterior' => $anterior === null ? null : json_encode($anterior, JSON_THROW_ON_ERROR),
            'nuevo' => $accion === 'eliminar' ? null : json_encode($modelo->getAttributes(), JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
