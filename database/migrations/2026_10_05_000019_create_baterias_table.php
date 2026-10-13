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
        Schema::create('baterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dron_robot_id')->constrained('drones_robots')->cascadeOnDelete();
            $table->string('codigo', 50)->unique();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->string('capacidad', 50)->nullable();
            $table->unsignedInteger('ciclos')->default(0);
            $table->string('estado', 50);
            $table->date('fecha_adquisicion')->nullable();
            $table->date('ultima_revision')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baterias');
    }
};
