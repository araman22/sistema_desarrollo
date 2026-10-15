<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movilidad_accesos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $t->boolean('puede_editar')->default(false);
            $t->timestamps();
        });
        Schema::create('movilidad_auditorias', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('usuario_id')->nullable();
            $t->string('accion');
            $t->string('tabla');
            $t->unsignedBigInteger('registro_id');
            $t->json('anterior')->nullable();
            $t->json('nuevo')->nullable();
            $t->timestamp('created_at');
        });
        Schema::create('vehiculo_documentos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehiculo_id')->constrained('vehiculos')->restrictOnDelete();
            $t->string('nombre');
            $t->string('ruta');
            $t->string('mime', 100);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_documentos');
        Schema::dropIfExists('movilidad_auditorias');
        Schema::dropIfExists('movilidad_accesos');
    }
};
