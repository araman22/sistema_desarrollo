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

            // Seguridad y catalogos de permisos.
            PermisoSeeder::class,
            RolSeeder::class,
            CajaChicaPermisoSeeder::class,
            UsuarioAdminSeeder::class,
        ]);

        // Usuarios y datos de prueba: nunca fuera del entorno local.
        if (app()->environment('local')) {
            $this->call([
                UsuariosPruebaSeeder::class,
                DatosPruebaSeeder::class,
            ]);
        }
    }
}
