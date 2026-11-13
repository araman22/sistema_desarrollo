<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El historial de movilidad pasa a la auditoría general sin perder registros.
        DB::table('movilidad_auditorias')->orderBy('id')->chunkById(500, function ($filas) {
            // usuario_id no tenía clave foránea: se descartan referencias a usuarios inexistentes.
            $usuarios = DB::table('usuarios')->whereIn('id', $filas->pluck('usuario_id')->filter())->pluck('id')->flip();
            DB::table('auditorias')->insert($filas->map(fn ($fila) => [
                'usuario_id' => $usuarios->has($fila->usuario_id) ? $fila->usuario_id : null,
                'accion' => $fila->accion,
                'tabla_afectada' => $fila->tabla,
                'registro_id' => $fila->registro_id,
                'valor_anterior' => $fila->anterior,
                'valor_nuevo' => $fila->nuevo,
                'direccion_ip' => null,
                'created_at' => $fila->created_at,
                'updated_at' => $fila->created_at,
            ])->all());
        });

        Schema::drop('movilidad_auditorias');
        // Acceso provisional anterior al sistema de roles; ya no se usa.
        Schema::drop('movilidad_accesos');
    }

    // Las tablas vuelven vacías: el historial queda en auditorias.
    public function down(): void
    {
        Schema::create('movilidad_accesos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $t->boolean('puede_editar')->default(false);
            $t->timestamps();
        });
        Schema::create('movilidad_auditorias', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('usuario_id')->nullable();
            $t->string('accion');
            $t->string('tabla');
            $t->unsignedBigInteger('registro_id');
            $t->json('anterior')->nullable();
            $t->json('nuevo')->nullable();
            $t->timestamp('created_at');
        });
    }
};
