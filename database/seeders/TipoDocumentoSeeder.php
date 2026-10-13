<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoDocumentoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Catalogo de tipos de documento.
     */
    public function run(): void
    {
        $tipos = [
            'Legajo',
            'Acta',
            'Informe',
            'Resolucion',
            'Actuacion',
            'Carta',
            'Certificado',
        ];

        foreach ($tipos as $nombre) {
            TipoDocumento::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
