<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * mantenible_id / mantenible_tipo forman una relacion polimorfica,
     * por eso no llevan foreign key sino un indice compuesto.
     */
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mantenible_id');
            $table->string('mantenible_tipo', 100);
            $table->string('tipo', 100);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->decimal('costo', 15, 2)->nullable();
            $table->string('estado', 50)->default('realizado');
            $table->text('resultado')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['mantenible_tipo', 'mantenible_id']);
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
