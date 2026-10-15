<?php

namespace Database\Seeders;

use App\Models\Arma;
use App\Models\ArmaModelo;
use Illuminate\Database\Seeder;

class ArmaSeeder extends Seeder
{
    /**
     * Numero de serie -> [marca, modelo, estado]. Requiere el catalogo que
     * carga ArmaModeloSeeder.
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const ARMAS = [
        'SN-0002' => ['Glock', '17', 'asignada'],
        'SN-0003' => ['SIG Sauer', 'P226', 'asignada'],
        'SN-0004' => ['Bersa', 'Thunder 9', 'disponible'],
        'SN-0005' => ['Colt', 'M4', 'disponible'],
        'SN-0006' => ['Benelli', 'M4', 'en_reparacion'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ARMAS as $numeroSerie => [$marca, $modelo, $estado]) {
            $modeloArma = ArmaModelo::where('marca', $marca)
                ->where('modelo', $modelo)
                ->firstOrFail();

            Arma::updateOrCreate(
                ['numero_serie' => $numeroSerie],
                [
                    'modelo_arma_id' => $modeloArma->id,
                    'estado' => $estado,
                    'fecha_adquisicion' => now()->subYears(2)->toDateString(),
                ],
            );
        }
    }
}
