<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Rol -> permisos por modulo. Cada modulo lista sus acciones, o '*' para
     * todas las acciones del modulo. Si el rol entero es '*' recibe todos los
     * permisos del sistema: es el caso del administrador.
     *
     * @var array<string, array{descripcion: string, permisos: array<string, array<int, string>|string>|string}>
     */
    private const ROLES = [
        Rol::ADMINISTRADOR => [
            'descripcion' => 'Acceso total a todas las funcionalidades del sistema.',
            'permisos' => '*',
        ],
        'encargado_oficina' => [
            'descripcion' => 'Gestiona policias, oficinas, proyectos y aprobaciones.',
            'permisos' => [
                'policias' => ['ver', 'crear', 'editar', 'exportar'],
                'oficinas' => ['ver', 'editar'],
                'proyectos' => '*',
                'licencias' => '*',
                'capacitaciones' => '*',
                'actividades' => '*',
                'incidencias' => ['ver', 'crear', 'editar', 'resolver'],
                'movimientos_economicos' => ['ver', 'exportar'],
                'auditorias' => ['ver'],
                'alertas' => ['ver', 'atender'],
            ],
        ],
        'soporte' => [
            'descripcion' => 'Administra equipamiento, inventario y mantenimientos tecnologicos.',
            'permisos' => [
                'equipamientos' => '*',
                'inventarios' => '*',
                'mantenimientos' => '*',
                'drones_robots' => '*',
                'baterias' => '*',
                'incidencias' => ['ver', 'crear', 'editar', 'resolver'],
            ],
        ],
        'administrativo' => [
            'descripcion' => 'Registra movimientos economicos, licencias, vehiculos y documentos.',
            'permisos' => [
                'movimientos_economicos' => ['ver', 'crear', 'editar', 'exportar'],
                'licencias' => ['ver', 'crear', 'editar'],
                'vehiculos' => ['ver', 'crear', 'editar'],
                'vehiculo_mantenimientos' => ['ver', 'crear', 'editar'],
                'documentos' => ['ver', 'crear', 'editar', 'descargar'],
                'policias' => ['ver'],
            ],
        ],
        'invitado' => [
            'descripcion' => 'Solo lectura sobre los modulos del sistema.',
            'permisos' => [
                'policias' => ['ver'],
                'oficinas' => ['ver'],
                'proyectos' => ['ver'],
                'actividades' => ['ver'],
                'inventarios' => ['ver'],
                'capacitaciones' => ['ver'],
            ],
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

            $rol->permisos()->sync($this->idsPermisos($configuracion['permisos']));
        }
    }

    /**
     * @param  array<string, array<int, string>|string>|string  $permisos
     * @return array<int, int>
     */
    private function idsPermisos(array|string $permisos): array
    {
        if ($permisos === '*') {
            return Permiso::pluck('id')->all();
        }

        $consulta = Permiso::query()->where(function ($query) use ($permisos) {
            foreach ($permisos as $modulo => $acciones) {
                $query->orWhere(function ($query) use ($modulo, $acciones) {
                    $query->where('modulo', $modulo);

                    if ($acciones !== '*') {
                        $query->whereIn('accion', $acciones);
                    }
                });
            }
        });

        return $consulta->pluck('id')->all();
    }
}
