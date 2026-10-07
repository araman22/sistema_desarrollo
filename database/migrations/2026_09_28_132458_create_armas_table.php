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
        Schema::create('armas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_arma_id')->nullable()->constrained('armas_modelos')->nullOnDelete();
            $table->string('numero_serie', 100)->nullable();
            $table->string('estado', 50)->default('disponible');
            $table->date('fecha_adquisicion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('modelo_arma_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('armas');
    }
};
