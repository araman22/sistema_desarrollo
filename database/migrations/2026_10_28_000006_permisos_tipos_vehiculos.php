<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ACCIONES = ['ver', 'crear', 'editar', 'eliminar'];

    // El catálogo de tipos usaba los permisos de vehículos: cada rol conserva las mismas acciones,
    // ahora con permisos propios que el administrador puede quitar sin afectar a los vehículos.
    public function up(): void
    {
        // En una base nueva los permisos los crean PermisoSeeder y RolSeeder.
        if (! DB::table('permisos')->exists()) {
            return;
        }
        $ahora = now();
        foreach (self::ACCIONES as $accion) {
            DB::table('permisos')->insertOrIgnore([
                'nombre' => 'tipos_vehiculos.'.$accion, 'descripcion' => 'Permite '.$accion.' registros de tipos de vehículos.',
                'modulo' => 'tipos_vehiculos', 'accion' => $accion, 'created_at' => $ahora, 'updated_at' => $ahora,
            ]);
            $nuevo = DB::table('permisos')->where('nombre', 'tipos_vehiculos.'.$accion)->value('id');
            $roles = DB::table('rol_permiso')->join('permisos', 'permisos.id', '=', 'rol_permiso.permiso_id')
                ->where('permisos.nombre', 'vehiculos.'.$accion)->pluck('rol_permiso.rol_id');
            foreach ($roles as $rolId) {
                DB::table('rol_permiso')->insertOrIgnore(['rol_id' => $rolId, 'permiso_id' => $nuevo, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        }
    }

    public function down(): void
    {
        $permisos = DB::table('permisos')->where('modulo', 'tipos_vehiculos')->pluck('id');
        DB::table('rol_permiso')->whereIn('permiso_id', $permisos)->delete();
        DB::table('permisos')->whereIn('id', $permisos)->delete();
    }
};
