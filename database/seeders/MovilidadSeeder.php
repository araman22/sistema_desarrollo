<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class MovilidadSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Automóvil', 'Camioneta', 'Utilitario', 'Moto', 'Camión', 'Ambulancia', 'Trailer', 'Minibus', 'Furgon', 'Cuatriciclo', 'Lancha', 'Grua'] as $nombre) {
            TipoVehiculo::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
