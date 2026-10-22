<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Catalogos: deben existir antes que las tablas que los referencian.
            FuncionSeeder::class,
            JerarquiaSeeder::class,
            ArmaModeloSeeder::class,
            ArmaSeeder::class,
            TipoDocumentoSeeder::class,
            TipoLicenciaSeeder::class,
            TipoVehiculoSeeder::class,
            MovilidadSeeder::class,

            // Seguridad y catalogos de permisos.
            PermisoSeeder::class,
            RolSeeder::class,
            FondoFijoPermisoSeeder::class,
            UsuarioAdminSeeder::class,
        ]);

        $this->call([
            UsuariosPruebaSeeder::class,
            DatosPruebaSeeder::class,
            MovilidadDemoSeeder::class,
        ]);
    }
}
