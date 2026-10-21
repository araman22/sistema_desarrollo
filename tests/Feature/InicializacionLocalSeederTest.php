<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Closure;
use Database\Seeders\InicializacionLocalSeeder;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
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
            $this->assertTrue($admin->tienePermiso('fondo_fijo.'.$accion));
        }
        foreach (['administrativo' => ['ver', 'crear', 'descargar'], 'encargado_oficina' => ['ver', 'descargar']] as $rol => $acciones) {
            $usuario = new Usuario(['rol_id' => Rol::where('nombre', $rol)->value('id')]);
            foreach ($acciones as $accion) {
                $this->assertTrue($usuario->tienePermiso('fondo_fijo.'.$accion));
            }
            $this->assertTrue($usuario->tienePermiso('movimientos_economicos.ver'));
        }
        foreach (['soporte', 'invitado'] as $rol) {
            $this->assertFalse((new Usuario(['rol_id' => Rol::where('nombre', $rol)->value('id')]))->tienePermiso('fondo_fijo.ver'));
        }
        $this->assertDatabaseCount('personas', 0);
        $this->assertDatabaseCount('policias', 0);
        $this->assertDatabaseCount('oficinas', 0);
        $this->post(route('login.store'), ['nombre_usuario' => 'admin_local', 'password' => $clave])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('fondo-fijo.index'))->assertOk();
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
        DB::listen(function ($consulta) {
            if (str_starts_with($consulta->sql, 'insert into "usuarios"')) {
                throw new RuntimeException('Fallo simulado de persistencia');
            }
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
        $this->assertSame((new ReflectionClass(PermisoSeeder::class))->getConstant('MODULOS'),
            (new ReflectionClass(InicializacionLocalSeeder::class))->getConstant('PERMISOS'));
    }

    public function test_catalogo_inconsistente_no_modifica_registros(): void
    {
        Permiso::create(['nombre' => 'fondo_fijo.ver', 'modulo' => 'otro', 'accion' => 'ver']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Catalogo inconsistente');
    }

    public function test_accion_incorrecta_o_nombre_ambiguo_se_rechazan(): void
    {
        $permiso = Permiso::create(['nombre' => 'documentos.ver', 'modulo' => 'documentos', 'accion' => 'editar']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Catalogo inconsistente');
        $permiso->update(['nombre' => 'DOCUMENTOS.VER', 'accion' => 'ver']);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Catalogo inconsistente');
    }

    public function test_permisos_personalizados_y_descripciones_no_se_sobrescriben(): void
    {
        $institucional = Permiso::create(['nombre' => 'fondo_fijo.ver', 'modulo' => 'fondo_fijo', 'accion' => 'ver', 'descripcion' => 'Propia']);
        $custom = Permiso::create(['nombre' => 'especial.consultar', 'modulo' => 'especial', 'accion' => 'consultar']);
        $antes = [$institucional->fresh()->getAttributes(), $custom->fresh()->getAttributes()];
        $this->ejecutar();
        $this->assertSame($antes, [$institucional->fresh()->getAttributes(), $custom->fresh()->getAttributes()]);
    }

    public static function clavesBcrypt(): array
    {
        return [
            'ascii 72 bytes' => ['Aa9!'.str_repeat('x', 68), true],
            'ascii 73 bytes' => ['Aa9!'.str_repeat('x', 69), false],
            'unicode 72 bytes' => ['Aa9!'.str_repeat('é', 34), true],
            'unicode 74 bytes' => ['Aa9!'.str_repeat('é', 35), false],
        ];
    }

    #[DataProvider('clavesBcrypt')]
    public function test_limite_bcrypt_se_mide_en_bytes(string $clave, bool $permitida): void
    {
        config(['hashing.driver' => 'bcrypt']);
        Hash::forgetDrivers();
        if (! $permitida) {
            $this->esperarConflicto(fn () => $this->ejecutar(clave: $clave), '72 bytes');

            return;
        }
        $this->ejecutar(clave: $clave);
        $this->assertTrue(Hash::check($clave, Usuario::firstOrFail()->contrasena));
    }

    public function test_argon2id_admite_contrasenas_mayores_a_72_bytes(): void
    {
        if (! defined('PASSWORD_ARGON2ID')) {
            $this->markTestSkipped('Argon2id no disponible en este PHP.');
        }
        config(['hashing.driver' => 'argon2id', 'hashing.argon.memory' => 1024, 'hashing.argon.time' => 1, 'hashing.argon.threads' => 1]);
        Hash::forgetDrivers();
        $clave = 'Aa9!'.str_repeat('é', 40);
        $this->ejecutar(clave: $clave);
        $this->assertTrue(Hash::check($clave, Usuario::firstOrFail()->contrasena));
    }

    public function test_auditoria_cli_cubre_creaciones_asignaciones_e_inicializacion(): void
    {
        $this->ejecutar();
        $auditorias = Auditoria::all();
        $this->assertCount(Permiso::count() + 12, $auditorias);
        foreach ($auditorias as $auditoria) {
            $this->assertNull($auditoria->usuario_id);
            $this->assertNull($auditoria->direccion_ip);
            $this->assertSame('CLI', $auditoria->valor_nuevo['origen']);
            $this->assertSame(InicializacionLocalSeeder::class, $auditoria->valor_nuevo['proceso']);
            $this->assertArrayNotHasKey('contrasena', $auditoria->valor_nuevo['datos']);
        }
        $this->assertCount(1, $auditorias->pluck('valor_nuevo')->pluck('lote')->unique());
        $this->assertDatabaseCount('usuarios', 1);
        foreach (Rol::all() as $rol) {
            $asignacion = $auditorias->where('accion', 'asignar_permisos')->firstWhere('registro_id', $rol->id);
            $this->assertSame($rol->permisos()->orderBy('nombre')->pluck('nombre')->all(), $asignacion->valor_nuevo['datos']['permisos']);
        }
        $this->assertSame(1, $auditorias->where('accion', 'inicializar_local')->count());
    }

    public static function fallosAuditoria(): array
    {
        return [
            'creacion' => ['crear', false],
            'asignacion' => ['asignar_permisos', false],
            'resumen final' => ['inicializar_local', false],
            'cancelacion silenciosa' => ['inicializar_local', true],
        ];
    }

    #[DataProvider('fallosAuditoria')]
    public function test_auditoria_obligatoria_fallida_revierte_toda_la_operacion(string $accion, bool $cancelar): void
    {
        Auditoria::creating(function ($auditoria) use ($accion, $cancelar) {
            if ($auditoria->accion === $accion) {
                if ($cancelar) {
                    return false;
                }
                throw new RuntimeException('Fallo auditoria obligatorio');
            }
        });
        $this->esperarConflicto(fn () => $this->ejecutar(), $cancelar ? 'auditoria obligatoria' : 'Fallo auditoria');
    }

    public static function esquemas(): array
    {
        return [
            'compatible' => ['compatible', true],
            'tabla faltante' => ['tabla', false],
            'motor MyISAM' => ['motor', false],
            'columna faltante' => ['columna', false],
            'sin autoincremento' => ['autoincremento', false],
            'policia obligatorio' => ['policia', false],
            'actor obligatorio' => ['actor', false],
            'sin PK' => ['pk', false],
            'sin unique usuario' => ['unique_usuario', false],
            'sin unique correo' => ['unique_correo', false],
            'sin unique permiso' => ['unique_permiso', false],
            'sin unique pivote' => ['unique_pivote', false],
            'FK ausente' => ['fk', false],
            'FK otra base' => ['fk_base', false],
            'FK destino incorrecto' => ['fk_destino', false],
            'FK borrado incorrecto' => ['fk_borrado', false],
            'FK update incorrecto' => ['fk_update', false],
            'claves desactivadas' => ['desactivadas', false],
            'base real diferente' => ['base', false],
        ];
    }

    #[DataProvider('esquemas')]
    public function test_preflight_mysql_con_metadatos_simulados(string $caso, bool $permitido): void
    {
        $tablas = [];
        $columnas = [];
        $indices = [];
        $claves = [];
        $sqlite = DB::connection()->getSchemaBuilder();
        foreach (['roles', 'permisos', 'usuarios', 'rol_permiso', 'auditorias', 'policias'] as $tabla) {
            $tablas[] = ['name' => $tabla, 'engine' => 'InnoDB'];
            $columnas[$tabla] = $sqlite->getColumns($tabla);
            $indices[$tabla] = $sqlite->getIndexes($tabla);
            $claves[$tabla] = array_map(fn ($clave) => array_merge($clave, ['foreign_schema' => 'desarrollo_sistema']), $sqlite->getForeignKeys($tabla));
        }
        if ($caso === 'tabla') {
            array_pop($tablas);
        }
        if ($caso === 'motor') {
            $tablas[0]['engine'] = 'MyISAM';
        }
        if ($caso === 'columna') {
            $columnas['usuarios'] = array_values(array_filter($columnas['usuarios'], fn ($c) => $c['name'] !== 'contrasena'));
        }
        foreach ($columnas['usuarios'] as &$columna) {
            if ($caso === 'autoincremento' && $columna['name'] === 'id') {
                $columna['auto_increment'] = false;
            }
            if ($caso === 'policia' && $columna['name'] === 'policia_id') {
                $columna['nullable'] = false;
            }
        }
        unset($columna);
        foreach ($columnas['auditorias'] as &$columna) {
            if ($caso === 'actor' && $columna['name'] === 'usuario_id') {
                $columna['nullable'] = false;
            }
        }
        unset($columna);
        if ($caso === 'pk') {
            $indices['roles'] = [];
        }
        foreach (['unique_usuario' => ['usuarios', ['nombre_usuario']], 'unique_correo' => ['usuarios', ['correo_electronico']], 'unique_permiso' => ['permisos', ['nombre']], 'unique_pivote' => ['rol_permiso', ['rol_id', 'permiso_id']]] as $clave => [$tabla, $campos]) {
            if ($caso === $clave) {
                $indices[$tabla] = array_values(array_filter($indices[$tabla], fn ($i) => $i['columns'] !== $campos));
            }
        }
        if ($caso === 'fk') {
            $claves['usuarios'] = [];
        }
        foreach ($claves['usuarios'] as &$clave) {
            if ($clave['columns'] === ['rol_id']) {
                if ($caso === 'fk_base') {
                    $clave['foreign_schema'] = 'otra';
                }
                if ($caso === 'fk_destino') {
                    $clave['foreign_table'] = 'policias';
                }
                if ($caso === 'fk_borrado') {
                    $clave['on_delete'] = 'cascade';
                }
                if ($caso === 'fk_update') {
                    $clave['on_update'] = 'cascade';
                }
            }
        }
        unset($clave);
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('getTables')->with('desarrollo_sistema')->andReturn($tablas);
        $builder->shouldReceive('getColumns')->andReturnUsing(fn ($tabla) => $columnas[$tabla]);
        $builder->shouldReceive('getIndexes')->andReturnUsing(fn ($tabla) => $indices[$tabla]);
        $builder->shouldReceive('getForeignKeys')->andReturnUsing(fn ($tabla) => $claves[$tabla]);
        $conexion = Mockery::mock(Connection::class);
        $conexion->shouldReceive('selectOne')->once()->with('SELECT DATABASE() AS base_actual, @@SESSION.foreign_key_checks AS claves_foraneas')
            ->andReturn((object) ['base_actual' => $caso === 'base' ? 'otra' : 'desarrollo_sistema', 'claves_foraneas' => $caso === 'desactivadas' ? 0 : 1]);
        $conexion->shouldReceive('getSchemaBuilder')->andReturn($builder);
        $conexion->shouldNotReceive('statement');
        $conexion->shouldNotReceive('insert');
        $antes = $this->snapshot();
        try {
            (new GuardiaInicializador)->esquema($conexion);
            $this->assertTrue($permitido);
        } catch (RuntimeException $e) {
            $this->assertFalse($permitido, $e->getMessage());
            $this->assertStringContainsString('Esquema rechazado', $e->getMessage());
        }
        $this->assertSame($antes, $this->snapshot());
    }

    public function test_esquema_incompatible_detiene_el_flujo_antes_de_escribir(): void
    {
        $this->app->bind(InicializacionLocalSeeder::class, InicializadorEsquemaIncompatible::class);
        $this->esperarConflicto(fn () => $this->ejecutar(nuevo: false), 'Esquema rechazado');
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
    protected function validarEsquema(Connection $conexion): void
    {
        // El preflight real se prueba con metadatos MySQL simulados, sin conectarse a MySQL.
        $this->validarDestino($conexion);
    }

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
    public function esquema(Connection $conexion): void
    {
        $this->validarEsquema($conexion);
    }

    public function validar(Connection $conexion): void
    {
        $this->validarDestino($conexion);
    }

    public function bloquear(Connection $conexion, Closure $operacion): void
    {
        $this->conBloqueo($conexion, $operacion);
    }
}

class InicializadorEsquemaIncompatible extends InicializadorEnMemoria
{
    protected function validarEsquema(Connection $conexion): void
    {
        throw new RuntimeException('Esquema rechazado: simulado.');
    }
}
