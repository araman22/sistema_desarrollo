<?php

namespace Tests\Feature;

use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\FondoFijoPermisoSeeder;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Acceso permitido y denegado segun docs/seguridad/matriz-roles-permisos.md.
 * Cada prueba usa los roles reales que carga RolSeeder.
 */
class SeguridadAccesoModulosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermisoSeeder::class, RolSeeder::class, FondoFijoPermisoSeeder::class]);
    }

    private function usuarioConRol(string $rol): Usuario
    {
        return Usuario::factory()->create([
            'rol_id' => Rol::where('nombre', $rol)->firstOrFail()->id,
        ]);
    }

    private function oficina(): Oficina
    {
        return Oficina::create(['nombre' => 'Oficina de prueba', 'estado' => 'activa']);
    }

    public function test_sin_login_redirige_al_login(): void
    {
        $this->get(route('oficinas.index'))->assertRedirect(route('login'));
    }

    public function test_invitado_puede_ver_oficinas(): void
    {
        $this->actingAs($this->usuarioConRol('invitado'))
            ->get(route('oficinas.index'))
            ->assertOk();
    }

    public function test_invitado_no_puede_abrir_el_alta_escribiendo_la_url(): void
    {
        $this->actingAs($this->usuarioConRol('invitado'))
            ->get(route('oficinas.create'))
            ->assertForbidden();
    }

    public function test_invitado_no_puede_crear_oficinas_con_un_post_directo(): void
    {
        $this->actingAs($this->usuarioConRol('invitado'))
            ->post(route('oficinas.store'), ['nombre' => 'Oficina intrusa', 'estado' => 'activa'])
            ->assertForbidden();

        $this->assertDatabaseMissing('oficinas', ['nombre' => 'Oficina intrusa']);
    }

    public function test_invitado_no_puede_modificar_oficinas_con_un_put_directo(): void
    {
        $oficina = $this->oficina();

        $this->actingAs($this->usuarioConRol('invitado'))
            ->put(route('oficinas.update', $oficina), ['nombre' => 'Nombre cambiado', 'estado' => 'activa'])
            ->assertForbidden();

        $this->assertDatabaseHas('oficinas', ['id' => $oficina->id, 'nombre' => 'Oficina de prueba']);
    }

    public function test_invitado_no_puede_eliminar_oficinas(): void
    {
        $oficina = $this->oficina();

        $this->actingAs($this->usuarioConRol('invitado'))
            ->delete(route('oficinas.destroy', $oficina))
            ->assertForbidden();

        $this->assertDatabaseHas('oficinas', ['id' => $oficina->id]);
    }

    public function test_encargado_puede_editar_pero_no_eliminar_oficinas(): void
    {
        $oficina = $this->oficina();
        $encargado = $this->usuarioConRol('encargado_oficina');

        $this->actingAs($encargado)
            ->get(route('oficinas.edit', $oficina))
            ->assertOk();

        $this->actingAs($encargado)
            ->delete(route('oficinas.destroy', $oficina))
            ->assertForbidden();

        $this->assertDatabaseHas('oficinas', ['id' => $oficina->id]);
    }

    public function test_soporte_puede_crear_inventario_pero_no_ver_documentos(): void
    {
        $soporte = $this->usuarioConRol('soporte');

        $this->actingAs($soporte)
            ->get(route('inventarios.create'))
            ->assertOk();

        $this->actingAs($soporte)
            ->get(route('documentos.index'))
            ->assertForbidden();
    }

    public function test_invitado_no_puede_eliminar_inventario(): void
    {
        $inventario = Inventario::create([
            'codigo' => 'INV-SEG-01',
            'descripcion' => 'Bien de prueba',
            'categoria' => 'Mobiliario',
            'estado' => 'disponible',
        ]);

        $this->actingAs($this->usuarioConRol('invitado'))
            ->delete(route('inventarios.destroy', $inventario))
            ->assertForbidden();

        $this->assertDatabaseHas('inventarios', ['id' => $inventario->id]);
    }

    public function test_administrativo_no_puede_ver_oficinas(): void
    {
        $this->actingAs($this->usuarioConRol('administrativo'))
            ->get(route('oficinas.index'))
            ->assertForbidden();
    }

    public function test_el_403_no_muestra_datos_del_modulo(): void
    {
        $this->oficina();

        $this->actingAs($this->usuarioConRol('administrativo'))
            ->get(route('oficinas.index'))
            ->assertForbidden()
            ->assertDontSee('Oficina de prueba');
    }
}