<?php

namespace Tests\Feature;

use App\Models\TipoVehiculo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Services\VehiculoAlertas;
use Database\Seeders\MovilidadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MovilidadParteATest extends TestCase
{
    use RefreshDatabase;

    private function usuario(array $permisos = []): Usuario
    {
        $rol = Rol::create(['nombre' => uniqid('rol-'), 'activo' => true]);
        foreach ($permisos as $nombre) {
            [$modulo, $accion] = explode('.', $nombre);
            $rol->permisos()->attach(Permiso::firstOrCreate(['nombre' => $nombre], compact('modulo', 'accion')));
        }

        return Usuario::factory()->create(['rol_id' => $rol->id]);
    }

    private function acceder(bool $editar = true): Usuario
    {
        $acciones = $editar ? ['ver', 'crear', 'editar', 'eliminar'] : ['ver'];
        $permisos = collect(['vehiculos', 'tipos_vehiculos', 'vehiculo_mantenimientos'])->crossJoin($acciones)->map(fn ($par) => implode('.', $par))->all();
        $u = $this->usuario($permisos);
        $this->actingAs($u);

        return $u;
    }

    private function datos(array $extra = []): array
    {
        return array_merge(['dominio' => 'AB123CD', 'tipo_vehiculo_id' => TipoVehiculo::obtener('Automóvil')->id, 'estado' => 'disponible', 'kilometraje' => 1000], $extra);
    }

    private function mantenimiento(array $extra = []): array
    {
        return array_merge(['tipo' => 'preventivo', 'descripcion' => 'Cambio de aceite', 'fecha' => today()->toDateString(), 'kilometraje' => 1200, 'costo' => '100.00'], $extra);
    }

    public function test_invitado_no_accede(): void
    {
        $this->get('/vehiculos')->assertRedirect(route('login'));
        $this->post('/vehiculos', $this->datos())->assertRedirect(route('login'));
    }

    public function test_usuario_sin_permiso_no_accede_por_url(): void
    {
        $this->actingAs($this->usuario());
        $v = Vehiculo::factory()->create();
        $this->get('/vehiculos')->assertForbidden();
        $this->get(route('vehiculos.show', $v))->assertForbidden();
        $this->delete(route('vehiculos.destroy', $v))->assertForbidden();
    }

    public function test_lector_consulta_pero_no_modifica(): void
    {
        $this->acceder(false);
        $v = Vehiculo::factory()->create();
        $this->get('/vehiculos')->assertOk();
        $this->post('/vehiculos', $this->datos())->assertForbidden();
        $this->put(route('vehiculos.update', $v), $this->datos())->assertForbidden();
    }

    public function test_alta_normaliza_dominio_y_audita(): void
    {
        $this->acceder();
        $this->post('/vehiculos', $this->datos(['dominio' => ' ab123cd ']))->assertRedirect();
        $this->assertDatabaseHas('vehiculos', ['dominio' => 'AB123CD']);
        $this->assertDatabaseHas('auditorias', ['accion' => 'crear', 'tabla_afectada' => 'vehiculos']);
    }

    public function test_dominio_duplicado_se_rechaza(): void
    {
        $this->acceder();
        Vehiculo::create($this->datos());
        $this->post('/vehiculos', $this->datos())->assertSessionHasErrors('dominio');
        $this->assertDatabaseCount('vehiculos', 1);
    }

    public function test_datos_invalidos_no_se_guardan(): void
    {
        $this->acceder();
        $this->post('/vehiculos', $this->datos(['kilometraje' => -1, 'estado' => 'inventado', 'responsable_id' => 999, 'tipo_vehiculo_id' => 999, 'anio' => 1800]))->assertSessionHasErrors(['kilometraje', 'estado', 'responsable_id', 'tipo_vehiculo_id', 'anio']);
        $this->assertDatabaseCount('vehiculos', 0);
    }

    public function test_fechas_incoherentes_y_seguro_incompleto(): void
    {
        $this->acceder();
        $this->post('/vehiculos', $this->datos(['ultima_revision' => '2026-01-20', 'proxima_revision' => '2026-01-10', 'numero_poliza' => '123']))->assertSessionHasErrors(['proxima_revision', 'compania_seguro', 'vencimiento_seguro']);
    }

    public function test_kilometraje_no_disminuye(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $this->put(route('vehiculos.update', $v), $this->datos(['kilometraje' => 900]))->assertSessionHasErrors('kilometraje');
        $this->assertSame(1000, $v->fresh()->kilometraje);
    }

    public function test_registro_inexistente_devuelve_404(): void
    {
        $this->acceder();
        $this->get('/vehiculos/9999')->assertNotFound();
    }

    public function test_todas_las_vistas_se_renderizan(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $t = $v->tipo;
        foreach ([route('vehiculos.index'), route('vehiculos.create'), route('vehiculos.show', $v), route('vehiculos.edit', $v), route('tipos-vehiculos.index'), route('tipos-vehiculos.create'), route('tipos-vehiculos.show', $t), route('tipos-vehiculos.edit', $t), route('vehiculos.mantenimientos.create', $v)] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_tipo_en_uso_no_se_elimina(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $this->delete(route('tipos-vehiculos.destroy', $v->tipo))->assertSessionHasErrors('tipo');
        $this->assertDatabaseHas('tipo_vehiculos', ['id' => $v->tipo_vehiculo_id]);
    }

    public function test_crud_tipos(): void
    {
        $this->acceder();
        $this->post('/tipos-vehiculos', ['nombre' => 'Camión'])->assertRedirect();
        $t = TipoVehiculo::where('nombre', 'Camión')->firstOrFail();
        $this->put(route('tipos-vehiculos.update', $t), ['nombre' => 'Camión pesado'])->assertRedirect();
        $this->delete(route('tipos-vehiculos.destroy', $t))->assertRedirect();
        $this->assertDatabaseMissing('tipo_vehiculos', ['id' => $t->id]);
    }

    public function test_mantenimiento_actualiza_km_y_audita(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $this->post(route('vehiculos.mantenimientos.store', $v), $this->mantenimiento())->assertRedirect();
        $this->assertSame(1200, $v->fresh()->kilometraje);
        $this->assertDatabaseHas('auditorias', ['tabla_afectada' => 'vehiculo_mantenimientos', 'accion' => 'crear']);
        // Una sola fila por operación: el modelo ya no se audita además por el trait automático.
        $this->assertSame(1, DB::table('auditorias')->where(['tabla_afectada' => 'vehiculo_mantenimientos', 'accion' => 'crear'])->count());
    }

    public function test_no_se_elimina_vehiculo_con_historial(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $v->mantenimientos()->create($this->mantenimiento());
        $this->delete(route('vehiculos.destroy', $v))->assertSessionHasErrors('vehiculo');
        $this->assertDatabaseHas('vehiculos', ['id' => $v->id]);
        $this->assertDatabaseCount('vehiculo_mantenimientos', 1);
    }

    public function test_vehiculo_sin_historial_se_elimina(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $this->delete(route('vehiculos.destroy', $v))->assertRedirect();
        $this->assertDatabaseMissing('vehiculos', ['id' => $v->id]);
    }

    public function test_no_se_puede_manipular_id_del_mantenimiento(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $otro = Vehiculo::create($this->datos(['dominio' => 'ZZ999ZZ']));
        $m = $otro->mantenimientos()->create($this->mantenimiento());
        $this->put(route('vehiculos.mantenimientos.update', [$v, $m]), $this->mantenimiento())->assertNotFound();
    }

    public function test_historial_no_acepta_kilometraje_incoherente(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $v->mantenimientos()->create($this->mantenimiento(['fecha' => today()->subDays(5)->toDateString(), 'kilometraje' => 1500]));
        $this->post(route('vehiculos.mantenimientos.store', $v), $this->mantenimiento(['kilometraje' => 1000]))->assertSessionHasErrors('kilometraje');
        $this->assertDatabaseCount('vehiculo_mantenimientos', 1);
    }

    public function test_mantenimiento_se_edita_y_no_se_borra(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $m = $v->mantenimientos()->create($this->mantenimiento());
        $this->get(route('vehiculos.mantenimientos.edit', [$v, $m]))->assertOk();
        $this->put(route('vehiculos.mantenimientos.update', [$v, $m]), $this->mantenimiento(['descripcion' => 'Corrección']))->assertRedirect();
        $this->assertDatabaseHas('vehiculo_mantenimientos', ['id' => $m->id, 'descripcion' => 'Corrección']);
        $this->delete(route('vehiculos.mantenimientos.destroy', [$v, $m]))->assertStatus(409);
    }

    public function test_vehiculo_dado_de_baja_no_admite_mantenimiento(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos(['estado' => 'baja']));
        $this->post(route('vehiculos.mantenimientos.store', $v), $this->mantenimiento())->assertSessionHasErrors('vehiculo');
    }

    public function test_mantenimiento_valida_costo_fecha_y_proximo_km(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $this->post(route('vehiculos.mantenimientos.store', $v), $this->mantenimiento(['costo' => -1, 'fecha' => today()->addDay()->toDateString(), 'proximo_kilometraje' => 100]))->assertSessionHasErrors(['costo', 'fecha', 'proximo_kilometraje']);
    }

    public function test_documento_privado_y_acceso_por_id(): void
    {
        $this->acceder();
        Storage::fake('local');
        $v = Vehiculo::create($this->datos());
        $this->post(route('vehiculos.documentos.store', $v), ['archivo' => UploadedFile::fake()->image('seguro.png')])->assertRedirect();
        $d = $v->documentos()->firstOrFail();
        Storage::disk('local')->assertExists($d->ruta);
        $this->get(route('vehiculos.documentos.download', [$v, $d]))->assertDownload();
        $otro = Vehiculo::create($this->datos(['dominio' => 'XY555ZZ']));
        $this->get(route('vehiculos.documentos.download', [$otro, $d]))->assertNotFound();
        $this->delete(route('vehiculos.destroy', $v))->assertSessionHasErrors('vehiculo');
        $this->actingAs($this->usuario());
        $this->get(route('vehiculos.documentos.download', [$v, $d]))->assertForbidden();
    }

    public function test_documentos_invalidos_se_rechazan(): void
    {
        $this->acceder();
        Storage::fake('local');
        $v = Vehiculo::create($this->datos());
        $this->post(route('vehiculos.documentos.store', $v), ['archivo' => UploadedFile::fake()->create('script.php', 10, 'text/plain')])->assertSessionHasErrors('archivo');
        $this->post(route('vehiculos.documentos.store', $v), ['archivo' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf')])->assertSessionHasErrors('archivo');
        $this->assertDatabaseCount('vehiculo_documentos', 0);
    }

    public function test_alertas_detectan_vencimientos_y_kilometraje(): void
    {
        $v = Vehiculo::create($this->datos(['vencimiento_seguro' => today()->subDay(), 'proxima_revision' => today()->addDays(10)]));
        $v->mantenimientos()->create($this->mantenimiento(['proximo_kilometraje' => 900]));
        $this->assertCount(3, VehiculoAlertas::para($v->load('mantenimientos')));
        $v->update(['estado' => 'baja']);
        $this->assertSame([], VehiculoAlertas::para($v));
    }

    public function test_responsable_activo_cambio_y_auditoria(): void
    {
        $this->acceder();
        $ids = [];
        foreach (['activo', 'activo', 'inactivo'] as $i => $estado) {
            $persona = DB::table('personas')->insertGetId(['dni' => '12345'.$i, 'nombre' => 'Nombre', 'apellido' => 'Apellido']);
            $ids[] = DB::table('policias')->insertGetId(['persona_id' => $persona, 'estado' => $estado]);
        }
        $v = Vehiculo::create($this->datos(['responsable_id' => $ids[0]]));
        $this->put(route('vehiculos.update', $v), $this->datos(['responsable_id' => $ids[1]]))->assertRedirect();
        $this->assertSame($ids[1], $v->fresh()->responsable_id);
        $a = DB::table('auditorias')->where('tabla_afectada', 'vehiculos')->where('accion', 'editar')->first();
        $this->assertSame($ids[0], json_decode($a->valor_anterior, true)['responsable_id']);
        $this->put(route('vehiculos.update', $v), $this->datos(['responsable_id' => $ids[2]]))->assertSessionHasErrors('responsable_id');
    }

    public function test_xss_se_escapa_y_campos_extra_no_se_asignan(): void
    {
        $this->acceder();
        $this->post('/vehiculos', $this->datos(['observaciones' => '<script>alert(1)</script>', 'id' => 999]))->assertRedirect();
        $v = Vehiculo::firstOrFail();
        $this->assertNotSame(999, $v->id);
        $this->get(route('vehiculos.show', $v))->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_login_y_logout(): void
    {
        $u = $this->usuario();
        $this->get('/movilidad/acceso')->assertRedirect(route('login'));
        $this->post(route('login.store'), ['nombre_usuario' => $u->nombre_usuario, 'password' => 'incorrecta'])->assertSessionHasErrors();
        $this->assertGuest();
        $this->post(route('login.store'), ['nombre_usuario' => $u->nombre_usuario, 'password' => '1234'])->assertRedirect();
        $this->assertAuthenticatedAs($u);
        $this->post(route('logout'))->assertRedirect();
        $this->assertGuest();
    }

    public function test_los_tipos_de_vehiculo_requieren_permisos_propios(): void
    {
        $permisos = collect(['ver', 'crear', 'editar', 'eliminar'])->map(fn ($accion) => "vehiculos.$accion")->all();
        $this->actingAs($this->usuario($permisos));
        $this->get('/vehiculos')->assertOk();
        $this->get(route('tipos-vehiculos.index'))->assertForbidden();
        $this->post('/tipos-vehiculos', ['nombre' => 'Camión'])->assertForbidden();
        $this->assertDatabaseMissing('tipo_vehiculos', ['nombre' => 'Camión']);
    }

    public function test_permiso_revocado_tiene_efecto_inmediato(): void
    {
        $u = $this->acceder();
        $u->rol->permisos()->detach();
        $this->get('/vehiculos')->assertForbidden();
    }

    public function test_factory_seeder_y_comando(): void
    {
        $this->seed(MovilidadSeeder::class);
        $this->assertDatabaseCount('tipo_vehiculos', 12);
        Vehiculo::factory()->create();
    }

    public function test_mantenimientos_del_mismo_dia_respetan_el_orden(): void
    {
        $this->acceder();
        $v = Vehiculo::create($this->datos());
        $this->post(route('vehiculos.mantenimientos.store', $v), $this->mantenimiento(['kilometraje' => 1200]))->assertRedirect();
        $this->post(route('vehiculos.mantenimientos.store', $v), $this->mantenimiento(['kilometraje' => 1300]))->assertRedirect();
        $primero = $v->mantenimientos()->orderBy('id')->firstOrFail();
        $this->put(route('vehiculos.mantenimientos.update', [$v, $primero]), $this->mantenimiento(['kilometraje' => 1400]))->assertSessionHasErrors('kilometraje');
        $this->assertDatabaseCount('vehiculo_mantenimientos', 2);
    }

    public function test_nombre_de_archivo_no_disfraza_contenido_prohibido(): void
    {
        $this->acceder();
        Storage::fake('local');
        $v = Vehiculo::create($this->datos());
        $ruta = tempnam(sys_get_temp_dir(), 'movilidad');
        file_put_contents($ruta, '<?php echo "malicioso";');
        try {
            $this->post(route('vehiculos.documentos.store', $v), ['archivo' => new UploadedFile($ruta, 'seguro.pdf', null, null, true)])->assertSessionHasErrors('archivo');
        } finally {
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }
        $this->assertDatabaseCount('vehiculo_documentos', 0);
    }

    public function test_lector_no_puede_adjuntar_documentos(): void
    {
        $this->acceder(false);
        Storage::fake('local');
        $v = Vehiculo::create($this->datos());
        $this->post(route('vehiculos.documentos.store', $v), ['archivo' => UploadedFile::fake()->image('foto.png')])->assertForbidden();
    }
}
