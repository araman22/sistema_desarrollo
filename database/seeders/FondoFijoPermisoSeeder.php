<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Asignacion aditiva: no modifica roles ni retira permisos existentes.
 * Puede ejecutarse por separado en instalaciones existentes.
 */
class FondoFijoPermisoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermisoSeeder::class);

        $asignaciones = [
            Rol::ADMINISTRADOR => ['ver', 'crear', 'descargar'],
            'administrativo' => ['ver', 'crear', 'descargar'],
            'encargado_oficina' => ['ver', 'descargar'],
        ];

        foreach ($asignaciones as $nombre => $acciones) {
            $rol = Rol::where('nombre', $nombre)->first();

            if ($rol) {
                $ids = Permiso::where('modulo', 'fondo_fijo')->whereIn('accion', $acciones)->pluck('id');
                $rol->permisos()->syncWithoutDetaching($ids);
            }
        }
    }
}
