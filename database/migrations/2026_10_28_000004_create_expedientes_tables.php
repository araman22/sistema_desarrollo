<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACCIONES = ['ver', 'crear', 'editar', 'eliminar', 'pasar', 'categorias'];

    // Roles existentes que reciben los permisos nuevos sin alterar el resto de su configuración.
    private const ROLES = [
        'administrador' => self::ACCIONES,
        'encargado_oficina' => self::ACCIONES,
        'administrativo' => ['ver', 'crear', 'editar', 'pasar'],
        'invitado' => ['ver'],
    ];

    public function up(): void
    {
        // Biblioratos o carpetas: categorías ampliables (notas emitidas, recibidas, memorándums...).
        Schema::create('biblioratos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bibliorato_id')->constrained('biblioratos')->restrictOnDelete();
            $table->string('numero', 50)->unique();
            $table->string('asunto', 255);
            $table->string('origen', 150);
            $table->string('destino', 150);
            $table->date('fecha_emision');
            $table->date('fecha_recepcion')->nullable();
            $table->string('estado', 30)->default('en_tramite');
            $table->foreignId('responsable_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->string('ubicacion_actual', 150);
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->index(['bibliorato_id', 'estado']);
            $table->index('fecha_emision');
        });

        Schema::create('expediente_documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->foreignId('documento_id')->constrained('documentos')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['expediente_id', 'documento_id']);
        });

        // Cada pase es un movimiento con envío y recepción; el recorrido completo es la lista ordenada.
        Schema::create('expediente_pases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->restrictOnDelete();
            $table->string('origen', 150);
            $table->string('destino', 150);
            $table->dateTime('fecha_envio');
            $table->dateTime('fecha_recepcion')->nullable();
            $table->foreignId('enviado_por_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->foreignId('recibido_por_id')->nullable()->constrained('policias')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->text('observaciones_recepcion')->nullable();
            $table->foreignId('usuario_envio_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('usuario_recepcion_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
            $table->index(['expediente_id', 'fecha_envio']);
            $table->index('fecha_recepcion');
        });

        $ahora = now();
        DB::table('biblioratos')->insert(collect(['Notas emitidas', 'Notas recibidas', 'Memorándums internos'])
            ->map(fn ($nombre) => ['nombre' => $nombre, 'activo' => true, 'created_at' => $ahora, 'updated_at' => $ahora])->all());

        // En una base nueva los permisos los crean PermisoSeeder y RolSeeder; aquí solo se completa una instalación existente.
        if (! DB::table('permisos')->exists()) {
            return;
        }
        foreach (self::ACCIONES as $accion) {
            DB::table('permisos')->insertOrIgnore([
                'nombre' => 'expedientes.'.$accion, 'descripcion' => 'Permite '.$accion.' registros de expedientes.',
                'modulo' => 'expedientes', 'accion' => $accion, 'created_at' => $ahora, 'updated_at' => $ahora,
            ]);
        }
        foreach (self::ROLES as $rol => $acciones) {
            $rolId = DB::table('roles')->where('nombre', $rol)->value('id');
            if (! $rolId) {
                continue;
            }
            foreach (DB::table('permisos')->where('modulo', 'expedientes')->whereIn('accion', $acciones)->pluck('id') as $permisoId) {
                DB::table('rol_permiso')->insertOrIgnore(['rol_id' => $rolId, 'permiso_id' => $permisoId, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        }
    }

    public function down(): void
    {
        $permisos = DB::table('permisos')->where('modulo', 'expedientes')->pluck('id');
        DB::table('rol_permiso')->whereIn('permiso_id', $permisos)->delete();
        DB::table('permisos')->whereIn('id', $permisos)->delete();

        Schema::dropIfExists('expediente_pases');
        Schema::dropIfExists('expediente_documento');
        Schema::dropIfExists('expedientes');
        Schema::dropIfExists('biblioratos');
    }
};
