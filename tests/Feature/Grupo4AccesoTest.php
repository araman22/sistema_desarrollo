<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Auditoria;
use App\Models\CajaChicaMovimiento;
use App\Models\MovimientoEconomico;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\CajaChicaPermisoSeeder;
use Database\Seeders\PermisoSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Grupo4AccesoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisoSeeder::class);
    }

    private function usuario(array $permisos = [], array $atributos = []): Usuario
    {
        $rol = Rol::create(['nombre' => fake()->unique()->slug(), 'activo' => true]);
        $rol->permisos()->sync(Permiso::whereIn('nombre', $permisos)->pluck('id'));

        return Usuario::factory()->create(array_merge([
            'rol_id' => $rol->id,
            'contrasena' => 'ClaveSegura123',
        ], $atributos));
    }

    private function movimiento(): CajaChicaMovimiento
    {
        return CajaChicaMovimiento::create([
            'tipo' => 'ingreso', 'monto' => '125.50', 'fecha' => '2026-10-01',
            'origen' => 'Fondo', 'ticket_path' => 'caja-chica/tickets/prueba.png',
        ]);
    }

    public function test_login_real_usa_contrasena_y_recordar_token(): void
    {
        $usuario = $this->usuario();
        $this->post(route('login.store'), [
            'nombre_usuario' => $usuario->nombre_usuario,
            'password' => 'ClaveSegura123', 'remember' => true,
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
        $this->assertNotNull($usuario->fresh()->ultimo_acceso);
        $this->assertSame('contrasena', $usuario->getAuthPasswordName());
        $this->assertSame('recordar_token', $usuario->getRememberTokenName());
    }

    public function test_login_rechaza_clave_incorrecta_y_limita_intentos(): void
    {
        $usuario = $this->usuario();
        $datos = ['nombre_usuario' => $usuario->nombre_usuario, 'password' => 'incorrecta'];
        for ($intento = 0; $intento < 5; $intento++) {
            $this->post(route('login.store'), $datos)->assertSessionHasErrors('nombre_usuario');
        }
        $datos['password'] = 'ClaveSegura123';
        $this->post(route('login.store'), $datos)->assertSessionHasErrors('nombre_usuario');
        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->usuario([], ['activo' => false]);
        $this->post(route('login.store'), [
            'nombre_usuario' => $usuario->nombre_usuario, 'password' => 'ClaveSegura123',
        ])->assertSessionHasErrors('nombre_usuario');
        $this->assertGuest();
    }

    public function test_recuperacion_envia_al_correo_y_restablece_contrasena(): void
    {
        Notification::fake();
        $usuario = $this->usuario();
        $this->assertSame($usuario->correo_electronico, $usuario->routeNotificationFor('mail'));
        $this->post(route('password.email'), [
            'correo_electronico' => $usuario->correo_electronico,
        ])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($usuario, ResetPassword::class, function ($notificacion) use (&$token) {
            $token = $notificacion->token;

            return true;
        });
        $this->post(route('password.update'), [
            'correo_electronico' => $usuario->correo_electronico, 'token' => $token,
            'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123',
        ])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NuevaClave123', $usuario->fresh()->contrasena));
        $this->post(route('login.store'), [
            'nombre_usuario' => $usuario->nombre_usuario, 'password' => 'NuevaClave123',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_invitados_deben_autenticarse_en_dashboard_y_caja(): void
    {
        $movimiento = $this->movimiento();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('caja-chica.index'))->assertRedirect(route('login'));
        $this->post(route('caja-chica.store'), [])->assertRedirect(route('login'));
        $this->get(route('caja-chica.ticket', $movimiento))->assertRedirect(route('login'));
    }

    public function test_sin_permisos_no_puede_listar_crear_descargar_ni_ver_saldos(): void
    {
        $movimiento = $this->movimiento();
        $this->actingAs($this->usuario());
        $this->get(route('caja-chica.index'))->assertForbidden();
        $this->post(route('caja-chica.store'), [])->assertForbidden();
        $this->get(route('caja-chica.ticket', $movimiento))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertDontSee('Total en caja')
            ->assertViewHas('stats', fn ($stats) => ! array_key_exists('ultimo_ingreso', $stats));
        $this->assertDatabaseCount('caja_chica_movimientos', 1);
    }

    public function test_lectura_no_concede_creacion_ni_descarga(): void
    {
        $movimiento = $this->movimiento();
        $this->actingAs($this->usuario(['caja_chica.ver']));
        $this->get(route('caja-chica.index'))->assertOk()->assertDontSee('Registrar ingreso');
        $this->post(route('caja-chica.store'), [])->assertForbidden();
        $this->get(route('caja-chica.ticket', $movimiento))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()->assertViewHas('stats', fn ($stats) => (float) $stats['diferencia_caja_chica'] === 125.50);
    }

    public function test_permiso_crear_registra_usuario_sin_copiar_movimiento_general(): void
    {
        $usuario = $this->usuario(['caja_chica.crear', 'caja_chica.ver']);
        $this->actingAs($usuario)->post(route('caja-chica.store'), [
            'tipo' => 'ingreso', 'monto' => '80.25', 'fecha' => '2026-10-01',
            'origen' => 'Fondo', 'usuario_id' => 999,
        ])->assertRedirect(route('caja-chica.index'));
        $this->assertDatabaseHas('caja_chica_movimientos', ['usuario_id' => $usuario->id, 'monto' => '80.25']);
        $this->assertDatabaseCount('movimientos_economicos', 0);
    }

    public function test_descarga_autorizada_usa_disco_privado_y_conserva_extension(): void
    {
        Storage::fake('local');
        $movimiento = $this->movimiento();
        Storage::disk('local')->put($movimiento->ticket_path, 'comprobante');
        $this->actingAs($this->usuario(['caja_chica.descargar']))
            ->get(route('caja-chica.ticket', $movimiento))->assertOk()->assertDownload('prueba.png');
        Storage::disk('local')->delete($movimiento->ticket_path);
        $this->get(route('caja-chica.ticket', $movimiento))->assertNotFound();
    }

    public function test_rol_inactivo_no_concede_acceso_a_caja(): void
    {
        $usuario = $this->usuario(['caja_chica.ver', 'caja_chica.crear', 'caja_chica.descargar']);
        $usuario->rol()->update(['activo' => false]);
        $this->actingAs($usuario);
        $this->get(route('caja-chica.index'))->assertForbidden();
        $this->post(route('caja-chica.store'), [])->assertForbidden();
        $this->get(route('caja-chica.ticket', $this->movimiento()))->assertForbidden();
    }

    public function test_desactivar_usuario_cierra_sesion_y_logout_funciona(): void
    {
        $usuario = $this->usuario(['caja_chica.ver']);
        $this->actingAs($usuario)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $usuario->update(['activo' => false]);
        $this->actingAs($usuario)->get(route('caja-chica.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_token_invalido_no_cambia_contrasena(): void
    {
        $usuario = $this->usuario();
        $hash = $usuario->contrasena;
        $this->post(route('password.update'), [
            'correo_electronico' => $usuario->correo_electronico, 'token' => 'invalido',
            'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123',
        ])->assertSessionHasErrors('correo_electronico');
        $this->assertSame($hash, $usuario->fresh()->contrasena);
    }

    public function test_policies_reutilizan_permisos_y_restringen_alertas_al_destinatario(): void
    {
        $usuario = $this->usuario([
            'movimientos_economicos.ver', 'movimientos_economicos.crear',
            'auditorias.ver', 'alertas.ver', 'alertas.atender',
        ]);
        $gate = Gate::forUser($usuario);
        $this->assertTrue($gate->allows('viewAny', MovimientoEconomico::class));
        $this->assertTrue($gate->allows('create', MovimientoEconomico::class));
        $this->assertFalse($gate->allows('delete', new MovimientoEconomico));
        $this->assertTrue($gate->allows('viewAny', Auditoria::class));
        $this->assertFalse($gate->allows('create', Auditoria::class));
        $this->assertFalse($gate->allows('update', new Auditoria));
        $propia = new Alerta(['usuario_id' => $usuario->id]);
        $ajena = new Alerta(['usuario_id' => $usuario->id + 1]);
        $this->assertTrue($gate->allows('view', $propia));
        $this->assertTrue($gate->allows('atender', $propia));
        $this->assertFalse($gate->allows('view', $ajena));
        $this->assertFalse($gate->allows('atender', $ajena));
        $this->assertFalse(Gate::forUser($this->usuario())->allows('viewAny', Auditoria::class));
    }

    public function test_seeder_aditivo_es_idempotente_y_preserva_personalizaciones(): void
    {
        $custom = $this->usuario(['alertas.ver']);
        $admin = Rol::create(['nombre' => Rol::ADMINISTRADOR, 'descripcion' => 'Personalizado', 'activo' => false]);
        $permiso = Permiso::where('nombre', 'alertas.ver')->firstOrFail();
        $permiso->update(['descripcion' => 'Descripcion personalizada']);
        $admin->permisos()->attach($permiso);
        $this->seed(CajaChicaPermisoSeeder::class);
        $this->seed(CajaChicaPermisoSeeder::class);
        $this->assertSame(4, $admin->permisos()->count());
        $this->assertSame('Personalizado', $admin->fresh()->descripcion);
        $this->assertFalse($admin->fresh()->activo);
        $this->assertSame(['alertas.ver'], $custom->permisos()->pluck('nombre')->all());
        $this->assertSame('Descripcion personalizada', $permiso->fresh()->descripcion);
    }
}
