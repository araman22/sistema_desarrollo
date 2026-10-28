<?php

namespace Database\Seeders;

use App\Models\TipoVehiculo;
use Illuminate\Database\Seeder;

class MovilidadSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Automóvil', 'Camioneta', 'Utilitario', 'Moto', 'Camión', 'Ambulancia', 'Trailer', 'Minibús', 'Furgón', 'Cuatriciclo', 'Lancha', 'Grúa'] as $nombre) {
            TipoVehiculo::obtener($nombre);
        }
    }
}
