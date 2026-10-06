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
        Schema::create('movimientos_economicos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50);
            $table->string('categoria', 100);
            $table->date('fecha');
            $table->string('concepto', 255);
            $table->text('descripcion')->nullable();
            $table->string('proveedor', 200)->nullable();
            $table->decimal('monto', 15, 2);
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->string('comprobante', 150)->nullable();
            $table->string('estado', 50)->default('registrado');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('tipo');
            $table->index('fecha');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_economicos');
    }
};
