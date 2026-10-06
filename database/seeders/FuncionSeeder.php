<?php

namespace Database\Seeders;

use App\Models\Funcion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FuncionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Catalogo de funciones que puede ocupar un Policia.
     */
    public function run(): void
    {
        $funciones = [
            'Administrador del sistema',
            'Oficial',
            'Sargento',
            'Cabo',
            'Analista',
            'Tecnico de soporte',
            'Perito',
            'Jefe de oficina',
        ];

        foreach ($funciones as $nombre) {
            Funcion::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
