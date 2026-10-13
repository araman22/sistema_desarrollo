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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('descripcion', 255);
            $table->string('categoria', 100);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->nullable();
            $table->string('estado', 50);
            $table->string('ubicacion', 255)->nullable();
            $table->foreignId('oficina_id')->nullable()->constrained('oficinas')->nullOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->date('fecha_adquisicion')->nullable();
            $table->decimal('valor_adquisicion', 15, 2)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('codigo_qr', 150)->unique()->nullable();
            $table->timestamps();

            $table->index('categoria');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
