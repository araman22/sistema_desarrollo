<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoVehiculoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Catalogo de tipos de vehiculo.
     */
    public function run(): void
    {
        $tipos = [
            'Utilitario',
            'Camioneta',
            'Automovil',
            'Moto',
            'Camion',
            'Trailer',
            'Ambulancia',
            'Minibus',
            'Furgon',
            'Cuatriciclo',
            'Lancha',
            'Grua',
        ];

        foreach ($tipos as $nombre) {
            TipoVehiculo::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
