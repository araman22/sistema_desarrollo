<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Esta tabla no lleva fecha_creacion / fecha_actualizacion: el registro de
     * auditoria ya tiene su propio campo fecha_hora.
     */
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('accion', 50);
            $table->string('tabla_afectada', 100);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('valor_anterior')->nullable();
            $table->json('valor_nuevo')->nullable();
            $table->string('direccion_ip', 45)->nullable();
            $table->dateTime('fecha_hora');

            $table->index(['tabla_afectada', 'registro_id']);
            $table->index('fecha_hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
