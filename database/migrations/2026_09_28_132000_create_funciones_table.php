<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catalogo de funciones que puede ocupar un Policia. Se crea antes de
     * `policias` (2026_09_28_133000) porque alli se agrega la FK
     * policias.funcion_id -> funciones.id.
     */
    public function up(): void
    {
        Schema::create('funciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funciones');
    }
};
