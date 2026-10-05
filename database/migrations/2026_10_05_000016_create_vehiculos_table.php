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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_vehiculo_id')->nullable()->constrained('tipo_vehiculos')->nullOnDelete();
            $table->string('dominio', 20)->unique();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('numero_chasis', 100)->nullable();
            $table->string('numero_motor', 100)->nullable();
            $table->unsignedInteger('kilometraje')->default(0);
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->string('estado', 50);
            $table->string('compania_seguro', 150)->nullable();
            $table->string('numero_poliza', 100)->nullable();
            $table->date('vencimiento_seguro')->nullable();
            $table->date('ultima_revision')->nullable();
            $table->date('proxima_revision')->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent();

            $table->index('estado');
            $table->index('proxima_revision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
