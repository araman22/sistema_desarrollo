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
        Schema::create('vehiculo_mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->string('tipo', 100);
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->unsignedInteger('kilometraje')->nullable();
            $table->decimal('costo', 15, 2)->nullable();
            $table->string('proveedor', 200)->nullable();
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->date('proxima_fecha')->nullable();
            $table->unsignedInteger('proximo_kilometraje')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['vehiculo_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo_mantenimientos');
    }
};
