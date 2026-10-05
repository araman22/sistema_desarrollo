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
        Schema::create('drones_robots', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50);
            $table->string('nombre', 150);
            $table->string('codigo', 50)->unique();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->string('estado', 50);
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('horas_uso', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('tipo');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drones_robots');
    }
};
