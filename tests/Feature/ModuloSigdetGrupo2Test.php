<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\Rol;
use App\Models\TipoDocumento;
use App\Models\Usuario;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModuloSigdetGrupo2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermisoSeeder::class, RolSeeder::class]);
    }

    protected function makeAdminUser(): Usuario
    {
        $rol = Rol::where('nombre', Rol::ADMINISTRADOR)->firstOrFail();

        return Usuario::factory()->create([
            'rol_id' => $rol->id,
            'nombre_usuario' => 'admin_test',
            'correo_electronico' => 'admin_test@example.com',
        ]);
    }

    protected function makeOficina(): Oficina
    {
        return Oficina::create([
            'nombre' => 'Dirección Central',
            'descripcion' => 'Sede principal',
            'ubicacion' => 'Piso 1',
            'telefono' => '1234',
            'correo_electronico' => 'central@example.com',
            'estado' => 'activa',
        ]);
    }

    public function test_usuario_autenticado_puede_ver_listado_de_oficinas_y_aplicar_filtros(): void
    {
        $this->makeOficina();
        $admin = $this->makeAdminUser();

        $this->actingAs($admin)
            ->get(route('oficinas.index', ['search' => 'Dirección', 'estado' => 'activa']))
            ->assertOk()
            ->assertSee('Dirección Central');
    }

    public function test_usuario_sin_permiso_no_puede_acceder_al_modulo(): void
    {
        $rolSinPermisos = Rol::create([
            'nombre' => 'sin_permisos_oficinas',
            'descripcion' => 'Rol de prueba sin permisos de oficinas.',
            'activo' => true,
        ]);

        $usuario = Usuario::factory()->create([
            'rol_id' => $rolSinPermisos->id,
            'nombre_usuario' => 'sin_permiso_oficinas',
            'correo_electronico' => 'sin_permiso_oficinas@example.com',
        ]);

        $this->actingAs($usuario)
            ->get(route('oficinas.index'))
            ->assertForbidden();
    }

    public function test_creacion_de_inventario_rechaza_codigo_duplicado(): void
    {
        $admin = $this->makeAdminUser();

        Inventario::create([
            'codigo' => 'INV-001',
            'descripcion' => 'Reservado',
            'categoria' => 'Tecnologia',
            'estado' => 'disponible',
        ]);

        $this->actingAs($admin)
            ->from(route('inventarios.create'))
            ->post(route('inventarios.store'), [
                'codigo' => 'INV-001',
                'descripcion' => 'Otra unidad',
                'categoria' => 'Tecnologia',
                'estado' => 'disponible',
            ])
            ->assertSessionHasErrors('codigo');
    }

    public function test_documento_con_archivo_invalido_no_puede_subirse(): void
    {
        $admin = $this->makeAdminUser();
        $tipo = TipoDocumento::firstOrCreate(['nombre' => 'Acta']);

        Storage::fake('local');

        $this->actingAs($admin)
            ->from(route('documentos.create'))
            ->post(route('documentos.store'), [
                'tipo_documento_id' => $tipo->id,
                'nombre' => 'Malware',
                'numero' => 99,
                'descripcion' => 'Archivo rechazado',
                'archivo' => UploadedFile::fake()->create('malware.exe', 500, 'application/x-msdownload'),
            ])
            ->assertStatus(422);
    }

    public function test_descarga_de_documento_no_autorizada_es_rechazada(): void
    {
        $admin = $this->makeAdminUser();
        $tipo = TipoDocumento::firstOrCreate(['nombre' => 'Informe']);

        $usuarioSinPermiso = Usuario::factory()->create([
            'rol_id' => Rol::where('nombre', 'invitado')->value('id'),
            'nombre_usuario' => 'sin_permiso_doc',
            'correo_electronico' => 'sin_permiso_doc@example.com',
        ]);

        $documento = Documento::create([
            'tipo_documento_id' => $tipo->id,
            'nombre' => 'Financiero',
            'numero' => 1,
            'descripcion' => 'Documento privado',
            'ruta_archivo' => 'documentos/prueba.pdf',
            'mime_type' => 'application/pdf',
            'tamano_bytes' => 1024,
            'extension' => 'pdf',
            'archivo_original' => 'prueba.pdf',
            'hash_archivo' => 'hash',
            'usuario_id' => $admin->id,
        ]);

        Storage::fake('local');
        Storage::disk('local')->put($documento->ruta_archivo, 'contenido de prueba');

        $this->actingAs($usuarioSinPermiso)
            ->get(route('documentos.download', $documento))
            ->assertForbidden();
    }

    public function test_se_puede_registrar_un_ingreso_y_un_egreso_y_mostrar_la_diferencia_en_dashboard(): void
    {
        $admin = $this->makeAdminUser();

        Storage::fake('local');

        $this->actingAs($admin)
            ->post(route('caja-chica.store'), [
                'tipo' => 'ingreso',
                'monto' => 15000,
                'fecha' => '2026-10-01',
                'origen' => 'Junta mensual',
                'destino' => '',
                'descripcion' => 'Ingreso por junta mensual',
            ])
            ->assertRedirect(route('caja-chica.index'))
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->post(route('caja-chica.store'), [
                'tipo' => 'egreso',
                'monto' => 4200,
                'fecha' => '2026-10-02',
                'origen' => '',
                'destino' => 'Compra de insumos',
                'descripcion' => 'Compra de papelera',
                'ticket' => UploadedFile::fake()->create('ticket.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('caja-chica.index'))
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Diferencia de caja chica')
            ->assertSee('10800.00');
    }
}
