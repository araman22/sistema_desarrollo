<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cada registro existente cuenta como una unidad; un lote puede cargar su cantidad real.
        Schema::table('inventarios', function (Blueprint $table) {
            $table->unsignedInteger('cantidad')->default(1)->after('categoria');
            $table->index(['oficina_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::table('inventarios', function (Blueprint $table) {
            $table->dropIndex(['oficina_id', 'categoria']);
            $table->dropColumn('cantidad');
        });
    }
};
