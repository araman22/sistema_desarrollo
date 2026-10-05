<?php

namespace Database\Seeders;

use App\Models\ArmaModelo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArmaModeloSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Catalogo de modelos de arma. `armas` guarda la unidad fisica.
     */
    public function run(): void
    {
        $modelos = [
            ['marca' => 'Browning', 'modelo' => 'Hi-Power', 'calibre' => '9mm'],
            ['marca' => 'Glock', 'modelo' => '17', 'calibre' => '9mm'],
            ['marca' => 'SIG Sauer', 'modelo' => 'P226', 'calibre' => '.40 S&W'],
            ['marca' => 'Colt', 'modelo' => 'M4', 'calibre' => '5.56x45mm'],
            ['marca' => 'Benelli', 'modelo' => 'M4', 'calibre' => '12ga'],
        ];

        foreach ($modelos as $modelo) {
            ArmaModelo::updateOrCreate(
                ['marca' => $modelo['marca'], 'modelo' => $modelo['modelo']],
                ['calibre' => $modelo['calibre']],
            );
        }
    }
}
