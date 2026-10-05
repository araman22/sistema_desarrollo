<?php

namespace Database\Seeders;

use App\Models\TipoLicencia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoLicenciaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Catalogo de tipos de licencia.
     */
    public function run(): void
    {
        $tipos = [
            'Permiso',
            'Licencia medica',
            'Licencia porophosphoros',
            'Licencia anual',
            'Comision',
        ];

        foreach ($tipos as $nombre) {
            TipoLicencia::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
