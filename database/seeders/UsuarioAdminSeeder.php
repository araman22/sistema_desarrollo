<?php

namespace Database\Seeders;

use App\Models\Arma;
use App\Models\ArmaModelo;
use App\Models\Funcion;
use App\Models\Jerarquia;
use App\Models\Oficina;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Usa los catalogos que cargan FuncionSeeder y ArmaModeloSeeder, por eso
     * DatabaseSeeder los debe llamar antes que a este seeder.
     */
    public function run(): void
    {
        $rolAdministrador = Rol::where('nombre', Rol::ADMINISTRADOR)->firstOrFail();

        $oficina = Oficina::updateOrCreate(
            ['nombre' => 'Direccion General'],
            [
                'descripcion' => 'Oficina central de la institucion.',
                'estado' => 'activa',
            ],
        );

        $jerarquia = Jerarquia::updateOrCreate(
            ['nombre' => 'Oficial'],
        );

        $funcion = Funcion::where('nombre', 'Administrador del sistema')->firstOrFail();

        $modeloArma = ArmaModelo::where('marca', 'Browning')
            ->where('modelo', 'Hi-Power')
            ->firstOrFail();

        $arma = Arma::updateOrCreate(
            ['numero_serie' => 'SN-0001'],
            [
                'modelo_arma_id' => $modeloArma->id,
                'estado' => 'disponible',
            ],
        );

        $persona = Persona::updateOrCreate(
            ['dni' => '00000000'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'del Sistema',
                'sexo' => 'No especificado',
            ],
        );

        $policia = Policia::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'jerarquia_id' => $jerarquia->id,
                'arma_id' => $arma->id,
                'oficina_id' => $oficina->id,
                'numero_legajo' => 'P-0001',
                'funcion_id' => $funcion->id,
                'fecha_ingreso' => now()->toDateString(),
                'estado' => 'activo',
            ],
        );

        // oficinas.responsable_id apunta a policias.id: la ficha laboral del
        // police es la que puede tener una oficina a su cargo.
        $oficina->update(['responsable_id' => $policia->id]);

        Usuario::updateOrCreate(
            ['nombre_usuario' => 'admin'],
            [
                'policia_id' => $policia->id,
                'rol_id' => $rolAdministrador->id,
                'correo_electronico' => 'admin@sigdet.local',
                'contrasena' => Hash::make('password'),
                'activo' => true,
            ],
        );
    }
}
