<?php

namespace App\Services;

use App\Models\Vehiculo;

class VehiculoAlertas
{
    public static function para(Vehiculo $v): array
    {
        if ($v->estado === 'baja') {
            return [];
        }
        $alertas = [];
        foreach (['vencimiento_seguro' => 'Seguro', 'proxima_revision' => 'Revisión'] as $campo => $nombre) {
            if (! $v->$campo) {
                $alertas[] = "$nombre sin fecha registrada";

                continue;
            }
            if ($v->$campo->lte(today()->addDays(30))) {
                $alertas[] = $nombre.($v->$campo->lt(today()) ? ' vencido: ' : ' próximo: ').$v->$campo->format('d/m/Y');
            }
        }
        $ultimo = $v->mantenimientos->sortByDesc(fn ($m) => $m->fecha->format('Y-m-d').sprintf('%020d', $m->id))->first();
        if ($ultimo && (($ultimo->proxima_fecha && $ultimo->proxima_fecha->lte(today()->addDays(30))) || ($ultimo->proximo_kilometraje !== null && $v->kilometraje >= $ultimo->proximo_kilometraje))) {
            $alertas[] = 'Mantenimiento pendiente por fecha o kilometraje';
        }

        return $alertas;
    }
}
