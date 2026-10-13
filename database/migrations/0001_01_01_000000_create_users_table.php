<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Restaura la migracion base del framework, pero creando la tabla usuarios
     * (no users) junto con las tablas de infraestructura que el proyecto necesita:
     * password_reset_tokens, porque config/auth.php la usa para el broker de
     * passwords, y sessions, porque .env define SESSION_DRIVER=database.
     *
     * La FK de usuarios hacia policia y rol se agrega despues en
     * create_usuarios_table, donde ya existen las tablas destino.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('policia_id')->nullable();
            $table->unsignedBigInteger('rol_id');
            $table->string('nombre_usuario', 100);
            $table->string('correo_electronico', 150)->nullable();
            $table->string('contrasena');
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->string('recordar_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
