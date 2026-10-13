<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * documentable_id / documentable_tipo forman una relacion polimorfica,
     * por eso no llevan foreign key sino un indice compuesto.
     */
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_documento_id')->nullable()->constrained('tipo_documentos')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->integer('numero');
            $table->string('descripcion', 255);
            $table->timestamps();

            $table->index('tipo_documento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
