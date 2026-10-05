<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * La tabla usuarios se crea en la migracion base del framework; aqui se
     * agregan las claves foraneas y los indices de acceso.
     *
     * Corre despues de create_policias (2026_10_05_000460) porque policias es
     * una de las tablas destino.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->unique('nombre_usuario');
            $table->unique('correo_electronico');

            $table->foreign('policia_id')->references('id')->on('policias')->nullOnDelete();

            // restrictOnDelete: no se puede borrar un rol que tenga usuarios
            // asignados, hay que reasignarlos primero.
            $table->foreign('rol_id')->references('id')->on('roles')->restrictOnDelete();

            $table->index('activo');
            $table->index('ultimo_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropForeign(['policia_id']);
            $table->dropUnique(['nombre_usuario']);
            $table->dropUnique(['correo_electronico']);
            $table->dropIndex(['activo']);
            $table->dropIndex(['ultimo_acceso']);
        });
    }
};
