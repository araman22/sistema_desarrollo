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
            'Automóvil',
            'Moto',
            'Camión',
            'Trailer',
            'Ambulancia',
            'Minibús',
            'Furgón',
            'Cuatriciclo',
            'Lancha',
            'Grúa',
        ];

        foreach ($tipos as $nombre) {
            TipoVehiculo::obtener($nombre);
        }
    }
}
