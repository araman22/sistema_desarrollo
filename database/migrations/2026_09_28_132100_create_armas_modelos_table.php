<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catalogo de modelos de arma (marca + modelo + calibre). `armas` guarda
     * la unidad fisica concreta: cual modelo es y su numero de serie.
     *
     * Se crea antes de `armas` (2026_09_28_132458) porque alli se agrega la
     * FK armas.modelo_arma_id -> armas_modelos.id.
     */
    public function up(): void
    {
        Schema::create('armas_modelos', function (Blueprint $table) {
            $table->id();
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->string('calibre', 50)->nullable();
            $table->timestamps();

            // Un modelo se identifica por la pareja marca + modelo, no por
            // cada campo por separado: "Browning" solo o "Hi-Power" solo
            // no son un modelo valido.
            $table->unique(['marca', 'modelo']);
            $table->index('calibre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('armas_modelos');
    }
};
