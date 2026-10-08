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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('codigo', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->text('objetivo')->nullable();
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->nullOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('prioridad', 30)->default('media');
            $table->string('estado', 50)->default('planificado');
            $table->decimal('porcentaje_avance', 5, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
