<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('estado_civil', 30)->nullable()->after('sexo');
        });

        // Cónyuge e hijos del agente.
        Schema::create('familiares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policia_id')->constrained('policias')->cascadeOnDelete();
            $table->string('parentesco', 20);
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('dni', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->timestamps();
            $table->index(['policia_id', 'parentesco']);
        });

        // Funciones adicionales; la principal sigue en policias.funcion_id.
        Schema::create('policia_funciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policia_id')->constrained('policias')->cascadeOnDelete();
            $table->foreignId('funcion_id')->constrained('funciones')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['policia_id', 'funcion_id']);
        });

        Schema::table('tipo_licencias', function (Blueprint $table) {
            $table->boolean('computa_cupo_anual')->default(false)->after('nombre');
        });
        DB::table('tipo_licencias')->where('nombre', 'Licencia anual')->update(['computa_cupo_anual' => true]);

        Schema::table('licencias', function (Blueprint $table) {
            $table->unsignedSmallInteger('anio_correspondiente')->nullable()->after('tipo_licencia_id');
            $table->date('fecha_regreso_prevista')->nullable()->after('fecha_fin');
            $table->date('fecha_regreso_efectivo')->nullable()->after('fecha_regreso_prevista');
            $table->boolean('requiere_reemplazo')->default(false);
            $table->foreignId('reemplazo_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->date('cobertura_desde')->nullable();
            $table->date('cobertura_hasta')->nullable();
            $table->text('cobertura_observaciones')->nullable();
            $table->index(['policia_id', 'anio_correspondiente']);
        });

        // Licencias anteriores: el año se toma de la salida y el regreso es el día siguiente al último.
        foreach (DB::table('licencias')->get(['id', 'fecha_inicio', 'cantidad_dias']) as $licencia) {
            $inicio = Carbon::parse($licencia->fecha_inicio);
            DB::table('licencias')->where('id', $licencia->id)->update([
                'anio_correspondiente' => $inicio->year,
                'fecha_regreso_prevista' => $inicio->copy()->addDays($licencia->cantidad_dias)->toDateString(),
            ]);
        }

        // Situación de revista: cada novedad conserva su período; nunca se pisa la anterior.
        Schema::create('novedades_revista', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policia_id')->constrained('policias')->cascadeOnDelete();
            $table->string('situacion', 30);
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->foreignId('licencia_id')->nullable()->unique()->constrained('licencias')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->index(['policia_id', 'fecha_desde']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedades_revista');

        Schema::table('licencias', function (Blueprint $table) {
            $table->dropIndex(['policia_id', 'anio_correspondiente']);
            $table->dropConstrainedForeignId('reemplazo_id');
            $table->dropColumn(['anio_correspondiente', 'fecha_regreso_prevista', 'fecha_regreso_efectivo', 'requiere_reemplazo', 'cobertura_desde', 'cobertura_hasta', 'cobertura_observaciones']);
        });

        Schema::table('tipo_licencias', function (Blueprint $table) {
            $table->dropColumn('computa_cupo_anual');
        });

        Schema::dropIfExists('policia_funciones');
        Schema::dropIfExists('familiares');

        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('estado_civil');
        });
    }
};
