<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proyecto_integrantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('policia_id')->constrained('policias')->cascadeOnDelete();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->text('observaciones')->nullable();

            $table->unique(['proyecto_id', 'policia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyecto_integrantes');
    }
};
