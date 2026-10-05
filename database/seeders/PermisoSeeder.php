<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Modulos del sistema con sus acciones tipificadas.
     *
     * @var array<string, array<int, string>>
     */
    private const MODULOS = [
        'usuarios' => ['ver', 'crear', 'editar', 'eliminar', 'restablecer'],
        'roles' => ['ver', 'crear', 'editar', 'eliminar', 'asignar'],
        'oficinas' => ['ver', 'crear', 'editar', 'eliminar'],
        'policias' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
        'licencias' => ['ver', 'crear', 'editar', 'eliminar', 'autorizar'],
        'capacitaciones' => ['ver', 'crear', 'editar', 'eliminar', 'inscribir'],
        'asignaciones' => ['ver', 'crear', 'editar', 'eliminar'],
        'actividades' => ['ver', 'crear', 'editar', 'eliminar'],
        'inventarios' => ['ver', 'crear', 'editar', 'eliminar'],
        'equipamientos' => ['ver', 'crear', 'editar', 'eliminar'],
        'vehiculos' => ['ver', 'crear', 'editar', 'eliminar'],
        'vehiculo_mantenimientos' => ['ver', 'crear', 'editar', 'eliminar'],
        'drones_robots' => ['ver', 'crear', 'editar', 'eliminar'],
        'baterias' => ['ver', 'crear', 'editar', 'eliminar'],
        'proyectos' => ['ver', 'crear', 'editar', 'eliminar'],
        'incidencias' => ['ver', 'crear', 'editar', 'eliminar', 'resolver'],
        'movimientos_economicos' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
        'documentos' => ['ver', 'crear', 'editar', 'eliminar', 'descargar'],
        'mantenimientos' => ['ver', 'crear', 'editar', 'eliminar'],
        'auditorias' => ['ver', 'exportar'],
        'alertas' => ['ver', 'crear', 'editar', 'eliminar', 'atender'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filas = [];

        foreach (self::MODULOS as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $filas[] = [
                    'nombre' => $modulo.'.'.$accion,
                    'descripcion' => 'Permite '.$accion.' registros de '.$modulo.'.',
                    'modulo' => $modulo,
                    'accion' => $accion,
                    'fecha_creacion' => now(),
                    'fecha_actualizacion' => now(),
                ];
            }
        }

        foreach (array_chunk($filas, 200) as $lote) {
            Permiso::insertOrIgnore($lote);
        }
    }
}
