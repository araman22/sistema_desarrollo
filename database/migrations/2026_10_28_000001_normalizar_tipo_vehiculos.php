<?php

use App\Models\TipoVehiculo;
use App\Services\UnificadorTiposVehiculo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_vehiculos', function (Blueprint $table) {
            $table->string('nombre_normalizado', 100)->nullable()->after('nombre');
        });

        // Los tipos equivalentes ya cargados ("Camión" / "Camion") se unifican antes de crear el índice.
        UnificadorTiposVehiculo::unificar();

        foreach (DB::table('tipo_vehiculos')->get(['id', 'nombre']) as $tipo) {
            DB::table('tipo_vehiculos')->where('id', $tipo->id)->update([
                'nombre' => TipoVehiculo::limpiar($tipo->nombre),
                'nombre_normalizado' => TipoVehiculo::normalizar($tipo->nombre),
            ]);
        }

        Schema::table('tipo_vehiculos', function (Blueprint $table) {
            // El índice sobre nombre usaba la collation general, que confunde ñ con n.
            $table->dropUnique(['nombre']);
            $columna = $table->string('nombre_normalizado', 100)->nullable(false);
            if (DB::getDriverName() === 'mysql') {
                $columna->collation('utf8mb4_bin');
            }
            $columna->change();
            $table->unique('nombre_normalizado');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_vehiculos', function (Blueprint $table) {
            $table->dropUnique(['nombre_normalizado']);
            $table->dropColumn('nombre_normalizado');
            $table->unique('nombre');
        });
    }
};
