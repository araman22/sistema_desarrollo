<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Rol -> modulos sobre los que tiene permisos. El simbolo * significa
     * todos los modulos: es el caso del administrador.
     *
     * @var array<string, array<int, string>|string>
     */
    private const ROLES = [
        'administrador' => [
            'descripcion' => 'Acceso total a todas las functionalities del sistema.',
            'modulos' => '*',
        ],
        'encargado_oficina' => [
            'descripcion' => 'Gestiona policias, oficinas, proyectos y aprobaciones.',
            'modulos' => ['policias', 'oficinas', 'proyectos', 'licencias', 'capacitaciones', 'actividades', 'incidencias', 'movimientos_economicos', 'auditorias', 'alertas'],
        ],
        'soporte' => [
            'descripcion' => 'Administra equipamiento, inventario y mantenimientos tecnologicos.',
            'modulos' => ['equipamientos', 'inventarios', 'mantenimientos', 'drones_robots', 'baterias', 'incidencias'],
        ],
        'administrativo' => [
            'descripcion' => 'Registra movimientos economicos, licencias y politicas.',
            'modulos' => ['movimientos_economicos', 'licencias', 'policias', 'documentos', 'vehiculos', 'vehiculo_mantenimientos'],
        ],
        'invitado' => [
            'descripcion' => 'Solo lectura sobre los modulos del sistema.',
            'modulos' => ['policias', 'oficinas', 'proyectos', 'actividades', 'inventarios', 'capacitaciones'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ROLES as $nombre => $configuracion) {
            $rol = Rol::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $configuracion['descripcion'],
                    'activo' => true,
                ],
            );

            if ($configuracion['modulos'] === '*') {
                $idsPermisos = Permiso::pluck('id')->all();
            } else {
                $idsPermisos = Permiso::whereIn('modulo', $configuracion['modulos'])->pluck('id')->all();
            }

            $rol->permisos()->sync($idsPermisos);
        }
    }
}
