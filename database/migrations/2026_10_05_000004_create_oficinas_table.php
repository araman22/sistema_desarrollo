<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cierra el ciclo oficinas <-> policias:
     * `oficinas.responsable_id` ya puede apuntar a `policias` porque esa
     * tabla se creo en 2026_09_28_133000, yrecien ahora se agrega la
     * FK inversa `policias.oficina_id` -> oficinas.id.
     */
    public function up(): void
    {
        Schema::create('oficinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('correo_electronico', 150)->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->string('estado', 30)->default('activa');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
        });

        Schema::table('policias', function (Blueprint $table) {
            $table->foreign('oficina_id')->references('id')->on('oficinas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policias', function (Blueprint $table) {
            $table->dropForeign(['oficina_id']);
        });

        Schema::dropIfExists('oficinas');
    }
};
