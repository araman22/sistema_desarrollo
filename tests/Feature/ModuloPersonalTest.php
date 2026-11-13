<?php

namespace Tests\Feature;

use App\Models\Funcion;
use App\Models\Jerarquia;
use App\Models\Licencia;
use App\Models\Permiso;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\Rol;
use App\Models\TipoLicencia;
use App\Models\Usuario;
use App\Services\Personal\GestorLicencias;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;
use Database\Seeders\TipoLicenciaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuloPersonalTest extends TestCase
{
    use RefreshDatabase;

    private TipoLicencia $anual;

    private TipoLicencia $medica;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-28 10:00:00');

        $this->seed([PermisoSeeder::class, RolSeeder::class, TipoLicenciaSeeder::class]);
        $this->anual = TipoLicencia::where('nombre', 'Licencia anual')->firstOrFail();
        $this->medica = TipoLicencia::where('nombre', 'Licencia medica')->firstOrFail();
        $this->actingAs(Usuario::factory()->create(['rol_id' => Rol::where('nombre', Rol::ADMINISTRADOR)->value('id')]));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function agente(string $ingreso = '2012-06-01', string $legajo = 'L-1'): Policia
    {
        $persona = Persona::create(['dni' => (string) random_int(10000000, 99999999), 'nombre' => 'Ana', 'apellido' => 'Pérez '.$legajo]);

        return Policia::create(['persona_id' => $persona->id, 'numero_legajo' => $legajo, 'fecha_ingreso' => $ingreso, 'estado' => 'activo']);
    }

    private function licencia(Policia $policia, array $cambios = []): array
    {
        return array_replace([
            'policia_id' => $policia->id,
            'tipo_licencia_id' => $this->anual->id,
            'anio_correspondiente' => 2026,
            'fecha_inicio' => '2026-01-05',
            'cantidad_dias' => 10,
            'estado' => 'pendiente',
        ], $cambios);
    }

    public function test_antiguedad_se_calcula_desde_la_fecha_de_ingreso(): void
    {
        $policia = $this->agente('2010-03-15');

        $this->assertSame(['anios' => 16, 'meses' => 7], $policia->antiguedad());
        $this->assertSame('16 años y 7 meses', $policia->antiguedad_texto);
        $this->assertSame(['anios' => 14, 'meses' => 11], $policia->antiguedad(Carbon::parse('2025-03-01')));
    }

    public function test_cupo_es_30_antes_del_anio_en_que_cumple_15_y_45_desde_ese_anio(): void
    {
        $policia = $this->agente('2012-06-01');

        $this->assertSame(30, GestorLicencias::cupoAnual($policia, 2026));
        $this->assertSame(45, GestorLicencias::cupoAnual($policia, 2027));
        $this->assertSame(45, GestorLicencias::cupoAnual($policia, 2030));
    }

    public function test_regreso_previsto_y_ultimo_dia_se_calculan_en_dias_corridos(): void
    {
        $policia = $this->agente();
        $this->post(route('licencias.store'), $this->licencia($policia))->assertSessionHasNoErrors();

        $licencia = Licencia::firstOrFail();
        $this->assertSame('2026-01-14', $licencia->fecha_fin->toDateString());
        $this->assertSame('2026-01-15', $licencia->fecha_regreso_prevista->toDateString());
    }

    public function test_no_se_puede_superar_el_cupo_anual_y_se_informa_lo_disponible(): void
    {
        $policia = $this->agente();
        $this->post(route('licencias.store'), $this->licencia($policia, ['cantidad_dias' => 25]))->assertSessionHasNoErrors();

        $this->post(route('licencias.store'), $this->licencia($policia, ['fecha_inicio' => '2026-03-01', 'cantidad_dias' => 6]))
            ->assertSessionHasErrors(['cantidad_dias' => 'Supera el cupo anual 2026: quedan 5 de 30 días corridos.']);
        $this->post(route('licencias.store'), $this->licencia($policia, ['fecha_inicio' => '2026-03-01', 'cantidad_dias' => 5]))->assertSessionHasNoErrors();

        $this->assertSame(['anio' => 2026, 'cupo' => 30, 'utilizados' => 30, 'disponibles' => 0], GestorLicencias::resumen($policia, 2026));
    }

    public function test_desde_el_anio_de_los_15_anios_se_admiten_45_dias(): void
    {
        $policia = $this->agente('2012-06-01');

        $this->post(route('licencias.store'), $this->licencia($policia, ['anio_correspondiente' => 2027, 'fecha_inicio' => '2027-01-04', 'cantidad_dias' => 45]))->assertSessionHasNoErrors();
        $this->post(route('licencias.store'), $this->licencia($policia, ['anio_correspondiente' => 2026, 'fecha_inicio' => '2026-02-02', 'cantidad_dias' => 31]))->assertSessionHasErrors('cantidad_dias');
    }

    public function test_rechazadas_canceladas_y_otros_tipos_no_consumen_cupo(): void
    {
        $policia = $this->agente();
        Licencia::create($this->licencia($policia, ['cantidad_dias' => 30, 'estado' => 'rechazada']));
        Licencia::create($this->licencia($policia, ['fecha_inicio' => '2026-02-01', 'cantidad_dias' => 30, 'estado' => 'cancelada']));
        Licencia::create($this->licencia($policia, ['tipo_licencia_id' => $this->medica->id, 'fecha_inicio' => '2026-03-01', 'cantidad_dias' => 20, 'estado' => 'autorizada']));

        $this->assertSame(0, GestorLicencias::diasUtilizados($policia, 2026));
        $this->post(route('licencias.store'), $this->licencia($policia, ['fecha_inicio' => '2026-05-04', 'cantidad_dias' => 30]))->assertSessionHasNoErrors();
    }

    public function test_al_editar_la_licencia_no_se_cuenta_dos_veces(): void
    {
        $policia = $this->agente();
        $this->post(route('licencias.store'), $this->licencia($policia, ['cantidad_dias' => 20]));
        $licencia = Licencia::firstOrFail();

        $this->put(route('licencias.update', $licencia), $this->licencia($policia, ['cantidad_dias' => 30]))->assertSessionHasNoErrors();
        $this->assertSame(30, $licencia->fresh()->cantidad_dias);
    }

    public function test_licencias_superpuestas_se_rechazan(): void
    {
        $policia = $this->agente();
        $this->post(route('licencias.store'), $this->licencia($policia));

        $this->post(route('licencias.store'), $this->licencia($policia, ['tipo_licencia_id' => $this->medica->id, 'fecha_inicio' => '2026-01-10', 'cantidad_dias' => 3]))
            ->assertSessionHasErrors(['fecha_inicio' => 'El agente ya tiene una licencia entre el 05/01/2026 y el 14/01/2026.']);
    }

    public function test_cobertura_exige_reemplazo_y_periodo_y_el_reemplazo_debe_estar_disponible(): void
    {
        $policia = $this->agente();
        $reemplazo = $this->agente('2015-01-01', 'L-2');

        $this->post(route('licencias.store'), $this->licencia($policia, ['requiere_reemplazo' => '1']))
            ->assertSessionHasErrors(['reemplazo_id', 'cobertura_desde', 'cobertura_hasta']);
        $this->post(route('licencias.store'), $this->licencia($policia, ['requiere_reemplazo' => '1', 'reemplazo_id' => $policia->id, 'cobertura_desde' => '2026-01-05', 'cobertura_hasta' => '2026-01-14']))
            ->assertSessionHasErrors('reemplazo_id');

        Licencia::create($this->licencia($reemplazo, ['tipo_licencia_id' => $this->medica->id, 'fecha_inicio' => '2026-01-08', 'cantidad_dias' => 2]));
        $this->post(route('licencias.store'), $this->licencia($policia, ['requiere_reemplazo' => '1', 'reemplazo_id' => $reemplazo->id, 'cobertura_desde' => '2026-01-05', 'cobertura_hasta' => '2026-01-14']))
            ->assertSessionHasErrors(['reemplazo_id' => 'El reemplazo elegido tiene una licencia en el mismo período.']);

        $this->post(route('licencias.store'), $this->licencia($policia, ['fecha_inicio' => '2026-02-02', 'requiere_reemplazo' => '1', 'reemplazo_id' => $reemplazo->id, 'cobertura_desde' => '2026-02-02', 'cobertura_hasta' => '2026-02-11', 'cobertura_observaciones' => 'Cubre guardia']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('licencias', ['policia_id' => $policia->id, 'reemplazo_id' => $reemplazo->id, 'requiere_reemplazo' => true]);
    }

    public function test_sin_permiso_de_autorizar_solo_se_cargan_pendientes(): void
    {
        $rol = Rol::create(['nombre' => 'carga_licencias', 'activo' => true]);
        $rol->permisos()->attach(Permiso::whereIn('nombre', ['licencias.ver', 'licencias.crear', 'licencias.editar'])->pluck('id'));
        $this->actingAs(Usuario::factory()->create(['rol_id' => $rol->id]));
        $policia = $this->agente();

        $this->post(route('licencias.store'), $this->licencia($policia, ['estado' => 'autorizada']))->assertSessionHasErrors('estado');
        $this->post(route('licencias.store'), $this->licencia($policia))->assertSessionHasNoErrors();
        $this->put(route('licencias.update', Licencia::firstOrFail()), $this->licencia($policia, ['estado' => 'autorizada']))->assertSessionHasErrors('estado');
    }

    public function test_licencia_autorizada_figura_en_la_situacion_de_revista_y_el_regreso_efectivo_la_acorta(): void
    {
        $policia = $this->agente();
        $this->post(route('licencias.store'), $this->licencia($policia, ['fecha_inicio' => '2026-10-20', 'cantidad_dias' => 15, 'estado' => 'autorizada']));
        $licencia = Licencia::firstOrFail();

        $this->assertSame('licencia', $policia->situacionActual());
        $this->assertDatabaseHas('novedades_revista', ['licencia_id' => $licencia->id, 'situacion' => 'licencia']);
        $this->assertSame('2026-11-03', $licencia->novedad->fecha_hasta->toDateString());

        $this->put(route('licencias.update', $licencia), $this->licencia($policia, ['fecha_inicio' => '2026-10-20', 'cantidad_dias' => 15, 'estado' => 'autorizada', 'fecha_regreso_efectivo' => '2026-10-27']))->assertSessionHasNoErrors();
        $this->assertSame('2026-10-26', $licencia->novedad()->first()->fecha_hasta->toDateString());
        $this->assertSame('activo', $policia->situacionActual());
    }

    public function test_novedad_nueva_cierra_la_anterior_y_conserva_el_historial(): void
    {
        $policia = $this->agente();
        $this->post(route('personal.novedades.store', $policia), ['situacion' => 'carpeta_medica', 'fecha_desde' => '2026-10-01'])->assertSessionHasNoErrors();
        $this->assertSame('carpeta_medica', $policia->situacionActual());

        $this->post(route('personal.novedades.store', $policia), ['situacion' => 'activo', 'fecha_desde' => '2026-10-20'])->assertSessionHasNoErrors();

        $this->assertSame('activo', $policia->situacionActual());
        $this->assertSame('carpeta_medica', $policia->situacionActual(Carbon::parse('2026-10-10')));
        $this->assertDatabaseHas('novedades_revista', ['situacion' => 'carpeta_medica', 'fecha_hasta' => '2026-10-19 00:00:00']);
        $this->post(route('personal.novedades.store', $policia), ['situacion' => 'licencia', 'fecha_desde' => '2026-10-21'])->assertSessionHasErrors('situacion');
    }

    public function test_alta_de_agente_con_datos_personales_y_funciones_adicionales_auditadas(): void
    {
        $jerarquia = Jerarquia::create(['nombre' => 'Sargento']);
        $principal = Funcion::create(['nombre' => 'Guardia']);
        $adicional = Funcion::create(['nombre' => 'Chofer']);
        $datos = ['apellido' => 'Gómez', 'nombre' => 'Luis', 'dni' => '30111222', 'estado_civil' => 'casado', 'direccion' => 'San Martín 100',
            'numero_legajo' => 'L-900', 'jerarquia_id' => $jerarquia->id, 'funcion_id' => $principal->id, 'fecha_ingreso' => '2011-02-01', 'estado' => 'activo'];

        $this->post(route('personal.store'), $datos + ['funciones_adicionales' => [$principal->id]])->assertSessionHasErrors('funciones_adicionales.0');
        $this->post(route('personal.store'), $datos + ['funciones_adicionales' => [$adicional->id]])->assertSessionHasNoErrors();

        $policia = Policia::where('numero_legajo', 'L-900')->firstOrFail();
        $this->assertSame('casado', $policia->persona->estado_civil);
        $this->assertSame(['Chofer'], $policia->funcionesAdicionales->pluck('nombre')->all());
        $this->assertDatabaseHas('auditorias', ['tabla_afectada' => 'policias', 'registro_id' => $policia->id, 'accion' => 'editar']);
    }

    public function test_solo_un_conyuge_y_varios_hijos(): void
    {
        $policia = $this->agente();
        $this->post(route('personal.familiares.store', $policia), ['parentesco' => 'conyuge', 'nombre' => 'Marta', 'apellido' => 'Ruiz'])->assertSessionHasNoErrors();
        $this->post(route('personal.familiares.store', $policia), ['parentesco' => 'conyuge', 'nombre' => 'Otra', 'apellido' => 'Persona'])->assertSessionHasErrors('parentesco');
        $this->post(route('personal.familiares.store', $policia), ['parentesco' => 'hijo', 'nombre' => 'Juan', 'apellido' => 'Pérez', 'fecha_nacimiento' => '2015-04-01'])->assertSessionHasNoErrors();
        $this->post(route('personal.familiares.store', $policia), ['parentesco' => 'hijo', 'nombre' => 'Sofía', 'apellido' => 'Pérez'])->assertSessionHasNoErrors();

        $this->assertSame(3, $policia->familiares()->count());
    }

    public function test_agente_con_historial_no_se_elimina(): void
    {
        $policia = $this->agente();
        Licencia::create($this->licencia($policia));

        $this->delete(route('personal.destroy', $policia))->assertSessionHasErrors('policia');
        $this->assertDatabaseHas('policias', ['id' => $policia->id]);
    }

    public function test_sin_permisos_no_accede_al_modulo(): void
    {
        $rol = Rol::create(['nombre' => 'sin_personal', 'activo' => true]);
        $this->actingAs(Usuario::factory()->create(['rol_id' => $rol->id]));
        $policia = $this->agente();

        $this->get(route('personal.index'))->assertForbidden();
        $this->get(route('personal.show', $policia))->assertForbidden();
        $this->get(route('licencias.index'))->assertForbidden();
        $this->post(route('personal.novedades.store', $policia), ['situacion' => 'activo', 'fecha_desde' => '2026-10-01'])->assertForbidden();
    }

    public function test_el_listado_no_consulta_la_situacion_agente_por_agente(): void
    {
        $consultas = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get(route('personal.index'))->assertOk();

            return count(DB::getQueryLog());
        };
        $this->agente(legajo: 'A-1');
        $this->post(route('personal.novedades.store', $this->agente(legajo: 'A-2')), ['situacion' => 'franco_servicio', 'fecha_desde' => '2026-10-27']);
        $conDos = $consultas();
        foreach (range(3, 8) as $i) {
            $this->agente(legajo: "A-$i");
        }

        $this->assertSame($conDos, $consultas());
        $this->get(route('personal.index'))->assertSee('Franco de servicio');
    }

    public function test_las_vistas_se_renderizan(): void
    {
        $policia = $this->agente();
        $this->post(route('licencias.store'), $this->licencia($policia, ['estado' => 'autorizada']));
        $licencia = Licencia::firstOrFail();

        foreach ([route('personal.index'), route('personal.index', ['situacion' => 'licencia']), route('personal.create'), route('personal.show', $policia), route('personal.edit', $policia),
            route('licencias.index'), route('licencias.create', ['policia_id' => $policia->id]), route('licencias.show', $licencia), route('licencias.edit', $licencia)] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
