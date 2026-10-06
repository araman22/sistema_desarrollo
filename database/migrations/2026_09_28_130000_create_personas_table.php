<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabla de identidad global: describe a la persona, no su puesto.
     * Es el unico lugar donde viven nombre, documento y datos de contacto.
     * Los datos laborales (puesto, oficina, jerarquia, estado) NO van aqui:
     * corresponden a la ficha del empleado y, para los Policias, a `policias`.
     */
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('correo_electronico', 150)->nullable();
            $table->string('foto', 255)->nullable();
            $table->timestamps();

            $table->index('apellido');
            $table->index(['apellido', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
