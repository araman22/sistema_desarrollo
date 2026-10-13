<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    /**
     * Usuario de prueba -> [rol, activo]. Solo para entorno local: la
     * contrasena de todos es "password".
     *
     * @var array<string, array{0: string, 1: bool}>
     */
    private const USUARIOS = [
        'encargado' => ['encargado_oficina', true],
        'soporte' => ['soporte', true],
        'administrativo' => ['administrativo', true],
        'invitado' => ['invitado', true],
        'inactivo' => ['invitado', false],
    ];

    /**
     * Run the database seeds.
     *
     * Requiere los roles que carga RolSeeder.
     */
    public function run(): void
    {
        foreach (self::USUARIOS as $nombreUsuario => [$nombreRol, $activo]) {
            $rol = Rol::where('nombre', $nombreRol)->firstOrFail();

            Usuario::updateOrCreate(
                ['nombre_usuario' => $nombreUsuario],
                [
                    'policia_id' => null,
                    'rol_id' => $rol->id,
                    'correo_electronico' => $nombreUsuario.'@sigdet.local',
                    'contrasena' => Hash::make('password'),
                    'activo' => $activo,
                ],
            );
        }
    }
}
