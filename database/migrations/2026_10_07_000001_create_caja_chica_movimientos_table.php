<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_chica_movimientos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 12, 2);
            $table->string('origen')->nullable();
            $table->string('destino')->nullable();
            $table->date('fecha');
            $table->text('descripcion')->nullable();
            $table->string('ticket_path')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_chica_movimientos');
    }
};
