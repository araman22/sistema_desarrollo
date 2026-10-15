<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->string('ruta_archivo', 255)->nullable();
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('archivo_original', 255)->nullable();
            $table->string('hash_archivo', 128)->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn([
                'ruta_archivo',
                'mime_type',
                'tamano_bytes',
                'extension',
                'archivo_original',
                'hash_archivo',
                'usuario_id',
            ]);
        });
    }
};
