<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No modificar datos existentes; comprobar ambas tablas antes de cualquier DDL.
        foreach (['drones_robots', 'baterias'] as $tabla) {
            if (DB::table($tabla)->whereNotNull('numero_serie')->groupBy('numero_serie')->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Hay números de serie repetidos en '.$tabla.'. Revisarlos antes de aplicar la migración.');
            }
        }
        foreach (['drones_robots', 'baterias'] as $tabla) {
            Schema::table($tabla, fn (Blueprint $table) => $table->unique('numero_serie'));
        }
    }

    public function down(): void
    {
        foreach (['drones_robots', 'baterias'] as $tabla) {
            Schema::table($tabla, fn (Blueprint $table) => $table->dropUnique(['numero_serie']));
        }
    }
};
