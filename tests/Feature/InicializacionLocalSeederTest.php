<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Closure;
use Database\Seeders\InicializacionLocalSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class InicializacionLocalSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // El reemplazo existe solo en esta suite: la clase real nunca acepta SQLite/testing.
        $this->app->bind(InicializacionLocalSeeder::class, InicializadorEnMemoria::class);
    }

    private function ejecutar(bool $nuevo = true, ?string $clave = null, ?string $confirmacion = null): void
    {
        $comando = $this->artisan('db:seed', ['--class' => InicializacionLocalSeeder::class])
            ->expectsQuestion('Nombre del administrador local', 'admin_local')
            ->expectsQuestion('Correo del administrador local', 'admin_local@example.test');

        if ($nuevo) {
            $clave ??= 'Aa9!'.Str::random(24);
            $comando->expectsQuestion('Contrasena (minimo 16 caracteres, mayusculas, minusculas, numeros y simbolos)', $clave)
                ->expectsQuestion('Confirmar contrasena', $confirmacion ?? $clave);
        }

        $comando->run();
    }

    private function snapshot(): array
    {
        $resultado = [];
        foreach (['roles', 'usuarios', 'permisos', 'rol_permiso', 'auditorias'] as $tabla) {
            $resultado[$tabla] = DB::table($tabla)->orderBy('id')->get()->toJson();
        }

        return $resultado;
    }

    private function esperarConflicto(Closure $operacion, string $mensaje): void
    {
        $antes = $this->snapshot();
        try {
            $operacion();
            $this->fail('La inicializacion debio detenerse.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString($mensaje, $e->getMessage());
        }
        $this->assertSame($antes, $this->snapshot());
    }

    public function test_crea_cinco_roles_admin_y_permisos_y_permite_login_real(): void
    {
        $clave = 'Aa9!'.Str::random(24);
        $this->ejecutar(clave: $clave);
        $this->assertDatabaseCount('roles', 5);
        $this->assertDatabaseCount('usuarios', 1);
        $admin = Usuario::firstOrFail();
        $this->assertSame('administrador', $admin->rol->nombre);
        $this->assertTrue($admin->activo);
        $this->assertNull($admin->policia_id);
        $this->assertNotSame($clave, $admin->contrasena);
        $this->assertTrue(Hash::check($clave, $admin->contrasena));
        $this->assertSame(Permiso::count(), $admin->rol->permisos()->count());
        foreach (['ver', 'crear', 'descargar'] as $accion) {
            $this->assertTrue($admin->tienePermiso('caja_chica.'.$accion));
        }
        foreach (['administrativo' => ['ver', 'crear', 'descargar'], 'encargado_oficina' => ['ver', 'descargar']] as $rol => $acciones) {
            $usuario = new Usuario(['rol_id' => Rol::where('nombre', $rol)->value('id')]);
            foreach ($acciones as $accion) {
                $this->assertTrue($usuario->tienePermiso('caja_chica.'.$accion));
            }
            $this->assertTrue($usuario->tienePermiso('movimientos_economicos.ver'));
        }
        foreach (['soporte', 'invitado'] as $rol) {
            $this->assertFalse((new Usuario(['rol_id' => Rol::where('nombre', $rol)->value('id')]))->tienePermiso('caja_chica.ver'));
        }
        $this->assertDatabaseCount('personas', 0);
        $this->assertDatabaseCount('policias', 0);
        $this->assertDatabaseCount('oficinas', 0);
        $this->post(route('login.store'), ['nombre_usuario' => 'admin_local', 'password' => $clave])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('caja-chica.index'))->assertOk();
        $this->assertStringNotContainsString($clave, DB::table('auditorias')->get()->toJson());
        $this->assertStringNotContainsString($admin->contrasena, DB::table('auditorias')->get()->toJson());
    }

    public function test_repetir_no_cambia_datos_ni_pide_contrasena(): void
    {
        $this->ejecutar();
        $antes = $this->snapshot();
        $this->travel(1)->days();
        $this->ejecutar(nuevo: false);
        $this->assertSame($antes, $this->snapshot());
    }

    public function test_preserva_roles_institucionales_y_usuarios_personalizados(): void
    {
        $permiso = Permiso::create(['nombre' => 'personalizado.ver', 'modulo' => 'personalizado', 'accion' => 'ver']);
        $rol = Rol::create(['nombre' => 'soporte', 'descripcion' => 'Propia', 'activo' => false]);
        $rol->permisos()->attach($permiso);
        $otro = Usuario::factory()->create(['rol_id' => $rol->id]);
        $rolAntes = $rol->fresh()->getAttributes();
        $usuarioAntes = $otro->fresh()->getAttributes();
        $this->ejecutar();
        $this->assertSame($rolAntes, $rol->fresh()->getAttributes());
        $this->assertSame($usuarioAntes, $otro->fresh()->getAttributes());
        $this->assertSame([$permiso->id], $rol->permisos()->pluck('permisos.id')->all());
        $this->assertDatabaseCount('roles', 5);
        $this->assertDatabaseCount('usuarios', 2);
    }

    public function test_conflicto_de_nombre_no_sobrescribe_usuario(): void
    {
        $rol = Rol::create(['nombre' => 'invitado']);
        Usuario::factory()->create(['rol_id' => $rol->id, 'nombre_usuario' => 'admin_local']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Conflicto');
    }

    public function test_conflicto_de_correo_no_eleva_usuario(): void
    {
        $rol = Rol::create(['nombre' => 'invitado']);
        Usuario::factory()->create(['rol_id' => $rol->id, 'correo_electronico' => 'admin_local@example.test']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Conflicto');
    }

    public function test_roles_duplicados_detienen_inicializacion(): void
    {
        Rol::create(['nombre' => 'soporte']);
        Rol::create(['nombre' => 'soporte']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'duplicados');
    }

    public function test_admin_inactivo_no_es_reactivado(): void
    {
        Rol::create(['nombre' => 'administrador', 'activo' => false]);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Conflicto');
    }

    public function test_nombre_institucional_con_otro_uso_de_mayusculas_es_conflicto(): void
    {
        Rol::create(['nombre' => 'ADMINISTRADOR']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'ambiguos');
    }

    public function test_admin_sin_permisos_no_es_modificado_y_revierte_roles_nuevos(): void
    {
        Rol::create(['nombre' => 'administrador', 'activo' => true]);
        $this->esperarConflicto(fn () => $this->ejecutar(), 'carece de permisos');
    }

    public function test_error_al_crear_usuario_revierte_permisos_roles_y_auditoria(): void
    {
        Usuario::creating(function () {
            throw new RuntimeException('Fallo simulado de persistencia');
        });
        $this->esperarConflicto(fn () => $this->ejecutar(), 'Fallo simulado');
    }

    public function test_clave_debil_y_confirmacion_incorrecta_no_guardan_registros(): void
    {
        $this->esperarConflicto(fn () => $this->ejecutar(clave: 'corta'), 'contrasena');
        $this->esperarConflicto(fn () => $this->ejecutar(confirmacion: 'distinta'), 'contrasena');
    }

    public function test_no_admite_ejecucion_no_interactiva(): void
    {
        $this->esperarConflicto(fn () => $this->artisan('db:seed', [
            '--class' => InicializacionLocalSeeder::class, '--no-interaction' => true,
        ])->run(), 'interactiva');
    }

    public function test_matriz_institucional_coincide_con_grupo_1(): void
    {
        $this->assertSame((new ReflectionClass(RolSeeder::class))->getConstant('ROLES'),
            (new ReflectionClass(InicializacionLocalSeeder::class))->getConstant('ROLES'));
    }

    public static function destinos(): array
    {
        return [
            'local permitido' => ['local', [], true],
            'produccion' => ['production', [], false],
            'testing real rechazado' => ['testing', [], false],
            'otro motor' => ['local', ['driver' => 'sqlite'], false],
            'otro host' => ['local', ['host' => 'localhost'], false],
            'otra base' => ['local', ['database' => 'otra'], false],
            'socket' => ['local', ['unix_socket' => '/tmp/mysql.sock'], false],
            'replica' => ['local', ['read' => ['host' => 'remoto']], false],
            'escritura alternativa' => ['local', ['write' => ['host' => 'remoto']], false],
        ];
    }

    #[DataProvider('destinos')]
    public function test_guardia_real_sin_conectar_a_mysql(string $entorno, array $cambios, bool $permitido): void
    {
        $this->app->detectEnvironment(fn () => $entorno);
        $config = array_merge(['driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'desarrollo_sistema'], $cambios);
        $conexion = Mockery::mock(Connection::class);
        $conexion->shouldReceive('getConfig')->andReturnUsing(fn ($clave) => $config[$clave] ?? null);
        $conexion->shouldNotReceive('getPdo');
        $conexion->shouldNotReceive('selectOne');
        $seeder = new GuardiaInicializador;
        if (! $permitido) {
            $this->expectException(RuntimeException::class);
        }
        $seeder->validar($conexion);
        $this->assertTrue($permitido);
    }

    public function test_bloqueo_mysql_se_libera_aun_ante_error(): void
    {
        $conexion = Mockery::mock(Connection::class);
        $nombre = 'sigdet:desarrollo_sistema:inicializacion_local';
        $conexion->shouldReceive('selectOne')->once()->with('SELECT GET_LOCK(?, 0) AS adquirido', [$nombre])
            ->andReturn((object) ['adquirido' => 1]);
        $conexion->shouldReceive('selectOne')->once()->with('SELECT RELEASE_LOCK(?) AS liberado', [$nombre])
            ->andReturn((object) ['liberado' => 1]);
        $this->expectExceptionMessage('fallo');
        (new GuardiaInicializador)->bloquear($conexion, function () {
            throw new RuntimeException('fallo');
        });
    }

    public function test_guardia_rechaza_replicas_aunque_laravel_haya_normalizado_la_conexion(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        config(['database.connections.destino.read' => ['host' => 'remoto']]);
        $conexion = Mockery::mock(Connection::class);
        $config = ['name' => 'destino', 'driver' => 'mysql', 'host' => '127.0.0.1', 'database' => 'desarrollo_sistema'];
        $conexion->shouldReceive('getConfig')->andReturnUsing(fn ($clave) => $config[$clave] ?? null);
        $conexion->shouldNotReceive('getPdo');
        $this->expectException(RuntimeException::class);
        (new GuardiaInicializador)->validar($conexion);
    }

    public function test_bloqueo_ocupado_no_ejecuta_operacion(): void
    {
        $conexion = Mockery::mock(Connection::class);
        $conexion->shouldReceive('selectOne')->once()->andReturn((object) ['adquirido' => 0]);
        $this->expectExceptionMessage('en curso');
        (new GuardiaInicializador)->bloquear($conexion, function () {
            $this->fail('No debe escribir si el bloqueo esta ocupado.');
        });
    }
}

// Adaptadores exclusivos de pruebas, no registrados en la aplicacion.
class InicializadorEnMemoria extends InicializacionLocalSeeder
{
    protected function validarDestino(Connection $conexion): void
    {
        if (! app()->environment('testing') || $conexion->getDriverName() !== 'sqlite'
            || $conexion->getDatabaseName() !== ':memory:') {
            throw new RuntimeException('Las pruebas requieren SQLite en memoria.');
        }
    }

    protected function conBloqueo(Connection $conexion, Closure $operacion): void
    {
        $operacion();
    }
}

class GuardiaInicializador extends InicializacionLocalSeeder
{
    public function validar(Connection $conexion): void
    {
        $this->validarDestino($conexion);
    }

    public function bloquear(Connection $conexion, Closure $operacion): void
    {
        $this->conBloqueo($conexion, $operacion);
    }
}
