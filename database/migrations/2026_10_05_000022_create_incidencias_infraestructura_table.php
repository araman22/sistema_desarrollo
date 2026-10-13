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
        Schema::create('incidencias_infraestructura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->nullOnDelete();
            $table->string('tipo', 100);
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_reporte');
            $table->string('prioridad', 30)->default('media');
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->string('estado', 50)->default('pendiente');
            $table->decimal('costo', 15, 2)->nullable();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_reporte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias_infraestructura');
    }
};
