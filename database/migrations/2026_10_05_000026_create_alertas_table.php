<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * alertable_id / alertable_tipo forman una relacion polimorfica,
     * por eso no llevan foreign key sino un indice compuesto.
     */
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 100);
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('alertable_id');
            $table->string('alertable_tipo', 100);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('prioridad', 30)->default('media');
            $table->dateTime('fecha_generacion');
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->dateTime('fecha_lectura')->nullable();
            $table->timestamps();

            $table->index(['alertable_tipo', 'alertable_id']);
            $table->index(['usuario_id', 'estado']);
            $table->index('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
