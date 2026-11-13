<?php

namespace App\Console\Commands;

use App\Services\UnificadorTiposVehiculo;
use Illuminate\Console\Command;

class UnificarTiposVehiculo extends Command
{
    protected $signature = 'tipos-vehiculos:unificar {--aplicar : Unifica los duplicados; sin esta opción solo los lista}';

    protected $description = 'Revisa y unifica tipos de vehículo que solo difieren en tildes, mayúsculas o espacios.';

    public function handle(): int
    {
        $duplicados = UnificadorTiposVehiculo::duplicados();
        if ($duplicados->isEmpty()) {
            $this->info('No hay tipos de vehículo duplicados.');

            return self::SUCCESS;
        }

        $this->table(['Se conserva', 'Se unifican', 'Vehículos a mover'], $duplicados->map(fn ($grupo) => [
            $grupo->first()->nombre,
            $grupo->slice(1)->pluck('nombre')->implode(', '),
            $grupo->slice(1)->sum('vehiculos'),
        ])->values()->all());

        if (! $this->option('aplicar')) {
            $this->comment('Revisión solamente. Ejecute con --aplicar para unificar.');

            return self::SUCCESS;
        }

        $resultado = UnificadorTiposVehiculo::unificar();
        $this->info(count($resultado).' grupo(s) unificado(s).');

        return self::SUCCESS;
    }
}
