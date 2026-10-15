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
            'Licencia por maternidad',
            'Licencia por paternidad',
            'Licencia por estudio',
            'Licencia por fallecimiento de familiar',
            'Licencia sin goce de haberes',
        ];

        foreach ($tipos as $nombre) {
            TipoLicencia::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
