<?php

namespace Database\Seeders;

use App\Models\Jerarquia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JerarquiaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $jerarquias = [
            'Agente',
            'Cabo',
            'Cabo Primero',
            'Sargento',
            'Sargento Primero',
            'Sargento Ayudante',
            'Oficial Ayudante',
            'Oficial Subinspector',
            'Oficial Inspector',
        ];
        foreach ($jerarquias as $nombre) {
            Jerarquia::updateOrCreate(['nombre' => $nombre]);
        }

    }
}
