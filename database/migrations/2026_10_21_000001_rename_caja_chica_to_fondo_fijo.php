<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renombra el módulo caja chica a fondo fijo sin editar la migración
     * original (2026_10_07_000001), que ya puede haber corrido en otras bases.
     *
     * Todo es condicional: si la tabla o los permisos no existen, no hace nada.
     */
    public function up(): void
    {
        if (Schema::hasTable('caja_chica_movimientos') && ! Schema::hasTable('fondo_fijo_movimientos')) {
            Schema::rename('caja_chica_movimientos', 'fondo_fijo_movimientos');
        }

        $this->renombrarPermisos('caja_chica', 'fondo_fijo', 'caja chica', 'fondo fijo');
    }

    public function down(): void
    {
        if (Schema::hasTable('fondo_fijo_movimientos') && ! Schema::hasTable('caja_chica_movimientos')) {
            Schema::rename('fondo_fijo_movimientos', 'caja_chica_movimientos');
        }

        $this->renombrarPermisos('fondo_fijo', 'caja_chica', 'fondo fijo', 'caja chica');
    }

    /**
     * Pasa los permisos del módulo $desde al módulo $hacia. Si el permiso
     * destino ya existe (por ejemplo, porque PermisoSeeder corrió antes), se
     * mueven sus asignaciones a roles y se borra el permiso viejo, porque
     * permisos.nombre es único.
     */
    private function renombrarPermisos(string $desde, string $hacia, string $textoDesde, string $textoHacia): void
    {
        if (! Schema::hasTable('permisos')) {
            return;
        }

        DB::transaction(function () use ($desde, $hacia, $textoDesde, $textoHacia) {
            $viejos = DB::table('permisos')->where('modulo', $desde)->get();

            foreach ($viejos as $permiso) {
                $nombreNuevo = $hacia.'.'.$permiso->accion;
                $descripcion = $permiso->descripcion === null
                    ? null
                    : str_replace([$desde, $textoDesde], [$hacia, $textoHacia], $permiso->descripcion);

                $existente = DB::table('permisos')->where('nombre', $nombreNuevo)->first();

                if ($existente === null) {
                    DB::table('permisos')->where('id', $permiso->id)->update([
                        'nombre' => $nombreNuevo,
                        'modulo' => $hacia,
                        'descripcion' => $descripcion,
                        'updated_at' => now(),
                    ]);

                    continue;
                }

                // El destino ya existe: se le pasan los roles que no tenga.
                $rolesConDestino = DB::table('rol_permiso')->where('permiso_id', $existente->id)->pluck('rol_id');

                DB::table('rol_permiso')
                    ->where('permiso_id', $permiso->id)
                    ->whereNotIn('rol_id', $rolesConDestino)
                    ->update(['permiso_id' => $existente->id]);

                DB::table('rol_permiso')->where('permiso_id', $permiso->id)->delete();
                DB::table('permisos')->where('id', $permiso->id)->delete();
            }
        });
    }
};
