<?php

namespace Tests\Feature;

use App\Models\Bibliorato;
use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Permiso;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\Rol;
use App\Models\TipoDocumento;
use App\Models\Usuario;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ModuloExpedientesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-28 10:00:00');

        $this->seed([PermisoSeeder::class, RolSeeder::class]);
        $this->actingAs(Usuario::factory()->create(['rol_id' => Rol::where('nombre', Rol::ADMINISTRADOR)->value('id')]));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function expediente(array $cambios = []): Expediente
    {
        return Expediente::create(array_replace([
            'bibliorato_id' => Bibliorato::where('nombre', 'Notas recibidas')->value('id'),
            'numero' => 'NR-001/2026',
            'asunto' => 'Pedido de equipamiento',
            'origen' => 'Jefatura',
            'destino' => 'Desarrollo Tecnológico',
            'fecha_emision' => '2026-10-01',
            'estado' => 'en_tramite',
            'ubicacion_actual' => 'Mesa de entradas',
        ], $cambios));
    }

    private function agente(): Policia
    {
        $persona = Persona::create(['dni' => (string) random_int(10000000, 99999999), 'nombre' => 'Ana', 'apellido' => 'Pérez']);

        return Policia::create(['persona_id' => $persona->id, 'numero_legajo' => uniqid('L-'), 'estado' => 'activo']);
    }

    public function test_biblioratos_iniciales_y_nuevas_categorias(): void
    {
        $this->assertEqualsCanonicalizing(['Notas emitidas', 'Notas recibidas', 'Memorándums internos'], Bibliorato::pluck('nombre')->all());

        $this->post(route('biblioratos.store'), ['nombre' => '  Resoluciones  '])->assertSessionHasNoErrors();
        $this->post(route('biblioratos.store'), ['nombre' => 'Resoluciones'])->assertSessionHasErrors('nombre');

        $resoluciones = Bibliorato::where('nombre', 'Resoluciones')->firstOrFail();
        $this->put(route('biblioratos.update', $resoluciones), ['nombre' => 'Resoluciones'])->assertSessionHasNoErrors();
        $this->assertFalse($resoluciones->fresh()->activo);
    }

    public function test_alta_con_todos_los_atributos_y_documentos_asociados(): void
    {
        $responsable = $this->agente();
        $documento = Documento::create(['tipo_documento_id' => TipoDocumento::create(['nombre' => 'Nota'])->id, 'nombre' => 'Nota 15', 'numero' => 15, 'descripcion' => 'Adjunto']);

        $this->post(route('expedientes.store'), [
            'bibliorato_id' => Bibliorato::where('nombre', 'Notas emitidas')->value('id'),
            'numero' => ' NE-10/2026 ', 'asunto' => 'Solicitud', 'origen' => 'Desarrollo', 'destino' => 'Jefatura',
            'fecha_emision' => '2026-10-20', 'fecha_recepcion' => '2026-10-21', 'estado' => 'en_tramite',
            'responsable_id' => $responsable->id, 'ubicacion_actual' => 'Desarrollo', 'documentos' => [$documento->id],
        ])->assertSessionHasNoErrors();

        $expediente = Expediente::where('numero', 'NE-10/2026')->firstOrFail();
        $this->assertSame([$documento->id], $expediente->documentos->pluck('id')->all());
        $this->assertSame($responsable->id, $expediente->responsable_id);
        $this->assertDatabaseHas('auditorias', ['tabla_afectada' => 'expedientes', 'registro_id' => $expediente->id, 'accion' => 'crear']);
    }

    public function test_recepcion_anterior_a_emision_y_numero_repetido_se_rechazan(): void
    {
        $this->expediente();
        $this->post(route('expedientes.store'), ['bibliorato_id' => Bibliorato::value('id'), 'numero' => 'NR-001/2026', 'asunto' => 'X', 'origen' => 'A', 'destino' => 'B',
            'fecha_emision' => '2026-10-10', 'fecha_recepcion' => '2026-10-05', 'estado' => 'en_tramite', 'ubicacion_actual' => 'A'])
            ->assertSessionHasErrors(['numero', 'fecha_recepcion']);
    }

    public function test_pase_envio_y_recepcion_actualizan_la_ubicacion_y_el_recorrido(): void
    {
        $expediente = $this->expediente();
        $envia = $this->agente();
        $recibe = $this->agente();

        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Asesoría Legal', 'fecha_envio' => '2026-10-02 09:00', 'enviado_por_id' => $envia->id, 'observaciones' => 'Para dictamen'])
            ->assertSessionHasNoErrors();
        $pase = $expediente->pases()->firstOrFail();
        $this->assertSame('Mesa de entradas', $pase->origen);
        $this->assertSame('Pendiente de recepción', $expediente->fresh()->estado_texto);
        $this->assertSame([$expediente->id], Expediente::pendientesDeRecepcion()->pluck('id')->all());

        $this->patch(route('expedientes.pases.recibir', [$expediente, $pase]), ['fecha_recepcion' => '2026-10-03 11:30', 'recibido_por_id' => $recibe->id])
            ->assertSessionHasNoErrors();

        $expediente->refresh();
        $this->assertSame('Asesoría Legal', $expediente->ubicacion_actual);
        $this->assertSame($recibe->id, $expediente->responsable_id);
        $this->assertSame('En trámite', $expediente->estado_texto);
        $this->assertSame(0, Expediente::pendientesDeRecepcion()->count());

        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Jefatura', 'fecha_envio' => '2026-10-05 08:00'])->assertSessionHasNoErrors();
        $this->assertSame([['Mesa de entradas', 'Asesoría Legal'], ['Asesoría Legal', 'Jefatura']],
            $expediente->pases()->orderBy('fecha_envio')->get()->map(fn ($p) => [$p->origen, $p->destino])->all());
        $this->get(route('expedientes.show', $expediente))->assertOk()->assertSeeInOrder(['Mesa de entradas', 'Asesoría Legal', 'Jefatura']);
    }

    public function test_no_se_admite_un_pase_nuevo_hasta_recibir_el_anterior(): void
    {
        $expediente = $this->expediente();
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00']);

        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Jefatura', 'fecha_envio' => '2026-10-02 10:00'])
            ->assertSessionHasErrors(['destino' => 'El expediente tiene un pase pendiente de recepción.']);
        $this->assertSame(1, $expediente->pases()->count());
    }

    public function test_fechas_incoherentes_en_pases_se_rechazan(): void
    {
        $expediente = $this->expediente();
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Mesa de entradas', 'fecha_envio' => '2026-10-02 09:00'])
            ->assertSessionHasErrors('destino');
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00']);
        $pase = $expediente->pases()->firstOrFail();

        $this->patch(route('expedientes.pases.recibir', [$expediente, $pase]), ['fecha_recepcion' => '2026-10-01 09:00'])->assertSessionHasErrors('fecha_recepcion');
        $this->patch(route('expedientes.pases.recibir', [$expediente, $pase]), ['fecha_recepcion' => '2026-10-04 09:00'])->assertSessionHasNoErrors();
        $this->patch(route('expedientes.pases.recibir', [$expediente, $pase]), ['fecha_recepcion' => '2026-10-05 09:00'])->assertSessionHasErrors(['fecha_recepcion' => 'El pase ya fue recibido.']);
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Jefatura', 'fecha_envio' => '2026-10-03 09:00'])->assertSessionHasErrors('fecha_envio');
    }

    public function test_expediente_archivado_no_recibe_pases_y_con_pases_no_se_elimina(): void
    {
        $archivado = $this->expediente(['numero' => 'A-1', 'estado' => 'archivado']);
        $this->post(route('expedientes.pases.store', $archivado), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00'])->assertSessionHasErrors('destino');

        $expediente = $this->expediente();
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00']);
        $this->delete(route('expedientes.destroy', $expediente))->assertSessionHasErrors('expediente');
        $this->assertDatabaseHas('expedientes', ['id' => $expediente->id]);
    }

    public function test_la_ubicacion_solo_cambia_con_pases(): void
    {
        $expediente = $this->expediente();
        $datos = $expediente->only(['bibliorato_id', 'numero', 'asunto', 'origen', 'destino', 'estado']) + ['fecha_emision' => '2026-10-01'];

        $this->put(route('expedientes.update', $expediente), $datos + ['ubicacion_actual' => 'Otra'])->assertSessionHasErrors('ubicacion_actual');
        $this->put(route('expedientes.update', $expediente), $datos + ['asunto' => 'Asunto corregido'])->assertSessionHasNoErrors();
        $this->assertSame('Mesa de entradas', $expediente->fresh()->ubicacion_actual);
    }

    public function test_filtro_de_pendientes_y_por_bibliorato(): void
    {
        $pendiente = $this->expediente(['numero' => 'P-1']);
        $this->expediente(['numero' => 'M-1', 'bibliorato_id' => Bibliorato::where('nombre', 'Memorándums internos')->value('id')]);
        $this->post(route('expedientes.pases.store', $pendiente), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00']);

        $this->assertSame(['P-1'], $this->get(route('expedientes.index', ['estado' => 'pendiente_recepcion']))->viewData('expedientes')->pluck('numero')->all());
        $this->assertSame(['M-1'], $this->get(route('expedientes.index', ['bibliorato_id' => Bibliorato::where('nombre', 'Memorándums internos')->value('id')]))->viewData('expedientes')->pluck('numero')->all());
    }

    public function test_permisos_separados_para_ver_pasar_y_categorias(): void
    {
        $expediente = $this->expediente();
        $rol = Rol::create(['nombre' => 'lector_expedientes', 'activo' => true]);
        $rol->permisos()->attach(Permiso::where('nombre', 'expedientes.ver')->pluck('id'));
        $this->actingAs(Usuario::factory()->create(['rol_id' => $rol->id]));

        $this->get(route('expedientes.show', $expediente))->assertOk()->assertDontSee('Enviar pase');
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00'])->assertForbidden();
        $this->post(route('biblioratos.store'), ['nombre' => 'Nueva'])->assertForbidden();
        $this->get(route('expedientes.create'))->assertForbidden();

        $this->actingAs(Usuario::factory()->create(['rol_id' => Rol::create(['nombre' => 'nada', 'activo' => true])->id]));
        $this->get(route('expedientes.index'))->assertForbidden();
    }

    public function test_los_pases_quedan_auditados(): void
    {
        $expediente = $this->expediente();
        $this->post(route('expedientes.pases.store', $expediente), ['destino' => 'Legal', 'fecha_envio' => '2026-10-02 09:00']);
        $pase = $expediente->pases()->firstOrFail();
        $this->patch(route('expedientes.pases.recibir', [$expediente, $pase]), ['fecha_recepcion' => '2026-10-03 09:00']);

        $this->assertDatabaseHas('auditorias', ['tabla_afectada' => 'expediente_pases', 'registro_id' => $pase->id, 'accion' => 'crear']);
        $this->assertDatabaseHas('auditorias', ['tabla_afectada' => 'expediente_pases', 'registro_id' => $pase->id, 'accion' => 'editar']);
        $this->assertDatabaseHas('auditorias', ['tabla_afectada' => 'expedientes', 'registro_id' => $expediente->id, 'accion' => 'editar']);
    }

    public function test_las_vistas_se_renderizan(): void
    {
        $expediente = $this->expediente();
        foreach ([route('expedientes.index'), route('expedientes.create'), route('expedientes.show', $expediente), route('expedientes.edit', $expediente), route('biblioratos.index')] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
