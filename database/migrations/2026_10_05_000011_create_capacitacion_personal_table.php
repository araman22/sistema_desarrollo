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
        Schema::create('capacitacion_personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacitacion_id')->constrained('capacitaciones')->cascadeOnDelete();
            $table->foreignId('policia_id')->constrained('policias')->cascadeOnDelete();
            $table->string('resultado', 100)->nullable();
            $table->text('observaciones')->nullable();

            $table->unique(['capacitacion_id', 'policia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacitacion_personal');
    }
};
