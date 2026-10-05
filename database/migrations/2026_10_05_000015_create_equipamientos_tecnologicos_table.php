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
        Schema::create('equipamientos_tecnologicos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('tipo', 100);
            $table->string('nombre', 150);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->string('procesador', 150)->nullable();
            $table->string('memoria_ram', 50)->nullable();
            $table->string('almacenamiento', 100)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('direccion_ip', 45)->nullable();
            $table->string('direccion_mac', 17)->nullable();
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->nullOnDelete();
            $table->string('estado', 50);
            $table->date('fecha_adquisicion')->nullable();
            $table->string('codigo_qr', 150)->unique()->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent();

            $table->index('tipo');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipamientos_tecnologicos');
    }
};
