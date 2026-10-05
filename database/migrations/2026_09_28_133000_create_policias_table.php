<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('personas')->cascadeOnDelete();
            $table->foreignId('jerarquia_id')->nullable()->constrained('jerarquias')->nullOnDelete();
            $table->foreignId('arma_id')->nullable()->constrained('armas')->nullOnDelete();
            $table->unsignedBigInteger('oficina_id')->nullable();
            $table->string('numero_legajo', 50)->nullable()->unique();
            $table->foreignId('funcion_id')->nullable()->constrained('funciones')->restrictOnDelete();
            $table->date('fecha_ingreso')->nullable();
            $table->string('estado', 30)->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent();

            $table->index('oficina_id');
            $table->index('estado');
            $table->index(['jerarquia_id', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policias');
    }
};
