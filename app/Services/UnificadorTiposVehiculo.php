<?php

namespace App\Services;

use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UnificadorTiposVehiculo
{
    /**
     * Grupos de tipos equivalentes (misma clave normalizada). Se calcula en PHP para
     * poder revisarlos incluso antes de que exista la columna nombre_normalizado.
     *
     * @return Collection<string, Collection<int, object>>
     */
    public static function duplicados(): Collection
    {
        return DB::table('tipo_vehiculos')
            ->leftJoin('vehiculos', 'vehiculos.tipo_vehiculo_id', '=', 'tipo_vehiculos.id')
            ->groupBy('tipo_vehiculos.id', 'tipo_vehiculos.nombre')
            ->orderBy('tipo_vehiculos.id')
            ->get(['tipo_vehiculos.id', 'tipo_vehiculos.nombre', DB::raw('count(vehiculos.id) as vehiculos')])
            ->groupBy(fn ($tipo) => TipoVehiculo::normalizar($tipo->nombre))
            ->filter(fn ($grupo) => $grupo->count() > 1);
    }

    /**
     * Conserva el tipo más antiguo de cada grupo (con su escritura original), mueve los
     * vehículos de los demás y los elimina. Los vehículos conservan su historial porque
     * solo cambia tipo_vehiculo_id.
     *
     * @return list<array{conservado: string, eliminados: list<string>, vehiculos: int}>
     */
    public static function unificar(): array
    {
        return DB::transaction(function () {
            $resultado = [];
            foreach (self::duplicados() as $grupo) {
                $conservado = $grupo->first();
                $otros = $grupo->slice(1);
                $movidos = 0;
                foreach (Vehiculo::whereIn('tipo_vehiculo_id', $otros->pluck('id'))->lockForUpdate()->get() as $vehiculo) {
                    $anterior = $vehiculo->getAttributes();
                    $vehiculo->update(['tipo_vehiculo_id' => $conservado->id]);
                    MovilidadAuditoria::registrar('unificar_tipo', $vehiculo, $anterior);
                    $movidos++;
                }
                foreach (TipoVehiculo::whereKey($otros->pluck('id'))->get() as $tipo) {
                    MovilidadAuditoria::registrar('eliminar', $tipo, $tipo->getAttributes() + ['unificado_en' => $conservado->id]);
                    $tipo->delete();
                }
                $resultado[] = ['conservado' => $conservado->nombre, 'eliminados' => $otros->pluck('nombre')->all(), 'vehiculos' => $movidos];
            }

            return $resultado;
        });
    }
}
