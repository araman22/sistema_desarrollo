<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Bateria;
use App\Models\DroneRobot;
use App\Models\Funcion;
use App\Models\Mantenimiento;
use App\Models\MantenimientoMovilidad;
use App\Models\Permiso;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\MovilidadDemoSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovilidadTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(?array $permisos = null): Usuario
    {
        $rol = Rol::create(['nombre' => uniqid('rol-'), 'activo' => true]);
        $permisos ??= collect(['drones_robots', 'baterias', 'mantenimientos'])->crossJoin(['ver', 'crear', 'editar', 'eliminar'])->map(fn ($par) => implode('.', $par))->all();
        foreach ($permisos as $nombre) {
            [$modulo, $accion] = explode('.', $nombre);
            $permiso = Permiso::firstOrCreate(['nombre' => $nombre], compact('modulo', 'accion'));
            $rol->permisos()->attach($permiso);
        }

        return Usuario::factory()->create(['rol_id' => $rol->id]);
    }

    private function droneDatos(array $cambios = []): array
    {
        return array_replace(['tipo' => 'dron', 'nombre' => 'Inspección aérea', 'codigo' => 'DR-001', 'estado' => 'operativo', 'horas_uso' => '10.50', 'fecha_adquisicion' => today()->subMonth()->toDateString()], $cambios);
    }

    private function bateriaDatos(DroneRobot $drone, array $cambios = []): array
    {
        return array_replace(['dron_robot_id' => $drone->id, 'codigo' => 'BAT-001', 'estado' => 'disponible', 'ciclos' => 10, 'fecha_adquisicion' => today()->subMonth()->toDateString()], $cambios);
    }

    private function mantenimientoDatos(array $cambios = []): array
    {
        return array_replace(['tipo' => 'revision', 'descripcion' => 'Control de funcionamiento', 'fecha' => today()->toDateString(), 'estado' => 'programado', 'costo' => '20.50'], $cambios);
    }

    private function policia(): Policia
    {
        $persona = Persona::create(['dni' => (string) random_int(10000000, 99999999), 'nombre' => 'Ana', 'apellido' => 'Prueba']);
        $funcion = Funcion::firstOrCreate(['nombre' => 'Técnico']);

        return Policia::create(['persona_id' => $persona->id, 'funcion_id' => $funcion->id, 'numero_legajo' => uniqid('L-'), 'estado' => 'activo']);
    }

    public function test_anonimos_cuentas_y_roles_inactivos_no_acceden(): void
    {
        $this->get('/movilidad/drones-robots')->assertRedirect(route('login'));
        $this->getJson('/movilidad/baterias')->assertUnauthorized();
        $usuario = $this->usuario();
        $usuario->update(['activo' => false]);
        $this->actingAs($usuario)->get('/movilidad/drones-robots')->assertRedirect(route('login'));
        $this->assertGuest();
        $usuario->update(['activo' => true]);
        $usuario->rol->update(['activo' => false]);
        $this->actingAs($usuario)->get('/movilidad/baterias')->assertForbidden();
    }

    public function test_sin_permisos_y_solo_lectura_no_pueden_modificar(): void
    {
        $drone = DroneRobot::factory()->create();
        $bateria = Bateria::factory()->create(['dron_robot_id' => $drone->id]);
        $this->actingAs($this->usuario([]))->get('/movilidad/drones-robots')->assertForbidden();
        $this->get('/movilidad/baterias')->assertForbidden();
        $this->actingAs($this->usuario(['drones_robots.ver', 'baterias.ver', 'mantenimientos.ver']));
        foreach (['drones-robots' => $drone, 'baterias' => $bateria] as $ruta => $recurso) {
            $this->get('/movilidad/'.$ruta)->assertOk();
            $this->get('/movilidad/'.$ruta.'/'.$recurso->id)->assertOk();
            $this->get('/movilidad/'.$ruta.'/create')->assertForbidden();
            $this->get('/movilidad/'.$ruta.'/'.$recurso->id.'/edit')->assertForbidden();
            $this->post('/movilidad/'.$ruta, [])->assertForbidden();
            $this->put('/movilidad/'.$ruta.'/'.$recurso->id, [])->assertForbidden();
            $this->delete('/movilidad/'.$ruta.'/'.$recurso->id)->assertForbidden();
        }
        $this->post('/movilidad/drones-robots/'.$drone->id.'/mantenimientos', $this->mantenimientoDatos())->assertForbidden();
        $this->get('/movilidad/drones-robots/'.$drone->id.'/mantenimientos')->assertOk();
    }

    public function test_crud_drones_robot_responsable_y_trazabilidad(): void
    {
        $usuario = $this->usuario();
        $responsable = $this->policia();
        $this->actingAs($usuario);
        $this->get('/movilidad/drones-robots/create')->assertOk();
        $this->post('/movilidad/drones-robots', $this->droneDatos(['id' => 999, 'responsable_id' => $responsable->id]))->assertRedirect();
        $drone = DroneRobot::firstOrFail();
        $this->assertNotEquals(999, $drone->id);
        $this->assertSame($responsable->id, $drone->responsable_id);
        $this->get('/movilidad/drones-robots/'.$drone->id.'/edit')->assertOk();
        $this->put('/movilidad/drones-robots/'.$drone->id, $this->droneDatos(['horas_uso' => '11.75', 'responsable_id' => null]))->assertRedirect();
        $this->assertNull($drone->fresh()->responsable_id);
        $auditoria = $drone->auditoriasDelRegistro()->where('accion', 'editar')->firstOrFail();
        $this->assertSame($usuario->id, $auditoria->usuario_id);
        $this->assertEquals($responsable->id, $auditoria->valor_anterior['responsable_id']);
        $this->assertNull($auditoria->valor_nuevo['responsable_id']);
        $this->assertEquals('11.75', $auditoria->valor_nuevo['horas_uso']);
        $this->get('/movilidad/drones-robots/'.$drone->id)->assertOk();
        $this->get('/movilidad/drones-robots?q=DR-001&tipo=dron')->assertOk()->assertSee('Inspección aérea');
        $this->post('/movilidad/drones-robots', $this->droneDatos(['tipo' => 'robot', 'codigo' => 'RB-001']))->assertRedirect();
        $this->delete('/movilidad/drones-robots/'.$drone->id)->assertRedirect();
        $this->assertDatabaseMissing('drones_robots', ['id' => $drone->id]);
        $this->assertNotNull($drone->auditoriasDelRegistro()->where('accion', 'eliminar')->first());
    }

    public function test_drones_rechazan_invalidos_duplicados_y_retroceso(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create(['horas_uso' => '15.00', 'numero_serie' => 'SERIE-1']);
        foreach ([['nombre' => ''], ['tipo' => 'avion'], ['estado' => 'invalido'], ['horas_uso' => '-1'], ['horas_uso' => '2.333'], ['horas_uso' => '100000000'], ['responsable_id' => 999], ['fecha_adquisicion' => today()->addDay()->toDateString()], ['codigo' => $drone->codigo], ['numero_serie' => 'SERIE-1']] as $cambio) {
            $this->postJson('/movilidad/drones-robots', $this->droneDatos($cambio))->assertUnprocessable()->assertJsonValidationErrors(array_key_first($cambio));
        }
        $this->putJson('/movilidad/drones-robots/'.$drone->id, $this->droneDatos(['codigo' => $drone->codigo, 'horas_uso' => '14.99']))->assertUnprocessable()->assertJsonValidationErrors('horas_uso');
        $this->assertDatabaseCount('drones_robots', 1);
    }

    public function test_baterias_crud_ciclos_asignacion_y_revision(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $otro = DroneRobot::factory()->create();
        $this->get('/movilidad/baterias/create')->assertOk();
        $this->post('/movilidad/baterias', $this->bateriaDatos($drone))->assertRedirect();
        $bateria = Bateria::firstOrFail();
        $this->get('/movilidad/baterias/'.$bateria->id.'/edit')->assertOk();
        $this->put('/movilidad/baterias/'.$bateria->id, $this->bateriaDatos($otro, ['ciclos' => 11]))->assertRedirect();
        $this->assertSame($otro->id, $bateria->fresh()->dron_robot_id);
        $this->assertSame(11, $bateria->fresh()->ciclos);
        $this->get('/movilidad/baterias/'.$bateria->id)->assertOk()->assertSee($otro->codigo);
        $this->delete('/movilidad/baterias/'.$bateria->id)->assertRedirect();
        $this->assertDatabaseMissing('baterias', ['id' => $bateria->id]);
    }

    public function test_baterias_rechazan_ciclos_fechas_y_asociaciones_invalidas(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $bateria = Bateria::factory()->create(['dron_robot_id' => $drone->id, 'ciclos' => 20, 'ultima_revision' => today()]);
        foreach ([['ciclos' => -1], ['ciclos' => '1.5'], ['ciclos' => '4294967296'], ['dron_robot_id' => 999], ['estado' => 'otra'], ['codigo' => $bateria->codigo], ['numero_serie' => $bateria->numero_serie], ['ultima_revision' => today()->subYear()->toDateString()]] as $cambio) {
            $this->postJson('/movilidad/baterias', $this->bateriaDatos($drone, $cambio))->assertUnprocessable()->assertJsonValidationErrors(array_key_first($cambio));
        }
        $this->putJson('/movilidad/baterias/'.$bateria->id, $this->bateriaDatos($drone, ['codigo' => $bateria->codigo, 'ciclos' => 19]))->assertUnprocessable();
        $this->putJson('/movilidad/baterias/'.$bateria->id, $this->bateriaDatos($drone, ['codigo' => $bateria->codigo, 'ciclos' => 20]))->assertUnprocessable()->assertJsonValidationErrors('ultima_revision');
        $drone->update(['estado' => 'baja']);
        $this->postJson('/movilidad/baterias', $this->bateriaDatos($drone))->assertUnprocessable()->assertJsonValidationErrors('dron_robot_id');
    }

    public function test_mantenimiento_polimorfico_de_dron_y_bateria(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $bateria = Bateria::factory()->create(['dron_robot_id' => $drone->id]);
        foreach (['drones-robots' => $drone, 'baterias' => $bateria] as $ruta => $recurso) {
            $base = '/movilidad/'.$ruta.'/'.$recurso->id.'/mantenimientos';
            $this->get($base.'/create')->assertOk();
            $this->post($base, $this->mantenimientoDatos())->assertRedirect($base);
            $mantenimiento = $recurso->mantenimientos()->firstOrFail();
            $this->assertInstanceOf($recurso::class, $mantenimiento->mantenible);
            $this->get($base.'/'.$mantenimiento->id)->assertOk();
            $this->get($base.'/'.$mantenimiento->id.'/edit')->assertOk();
            $this->put($base.'/'.$mantenimiento->id, $this->mantenimientoDatos(['estado' => 'realizado', 'resultado' => 'Funcionamiento correcto']))->assertRedirect();
            $this->get($base)->assertOk()->assertSee('Control de funcionamiento');
            $this->putJson($base.'/'.$mantenimiento->id, $this->mantenimientoDatos())->assertForbidden();
            $this->delete($base.'/'.$mantenimiento->id)->assertForbidden();
            $this->get('/movilidad/'.$ruta.'/'.$recurso->id)->assertOk();
        }
        $this->assertEquals(today()->toDateString(), $bateria->fresh()->ultima_revision->toDateString());
    }

    public function test_solo_programados_se_eliminan_y_cancelados_conservan_historial(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $base = '/movilidad/drones-robots/'.$drone->id.'/mantenimientos';
        $this->post($base, $this->mantenimientoDatos(['fecha' => today()->addWeek()->toDateString()]))->assertRedirect();
        $m = $drone->mantenimientos()->firstOrFail();
        $this->delete($base.'/'.$m->id)->assertRedirect();
        $this->assertDatabaseMissing('mantenimientos', ['id' => $m->id]);
        $this->post($base, $this->mantenimientoDatos())->assertRedirect();
        $cancelado = $drone->mantenimientos()->firstOrFail();
        $this->put($base.'/'.$cancelado->id, $this->mantenimientoDatos(['estado' => 'cancelado']))->assertRedirect();
        $this->delete($base.'/'.$cancelado->id)->assertForbidden();
        $this->assertDatabaseHas('mantenimientos', ['id' => $cancelado->id, 'estado' => 'cancelado']);
        $this->put($base.'/'.$cancelado->id, $this->mantenimientoDatos())->assertForbidden();
    }

    public function test_ids_ajenos_inexistentes_y_tipos_inyectados_se_rechazan(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $otro = DroneRobot::factory()->create();
        $m = $otro->mantenimientos()->create($this->mantenimientoDatos());
        $base = '/movilidad/drones-robots/'.$drone->id.'/mantenimientos';
        $this->get($base.'/'.$m->id)->assertNotFound();
        $this->put($base.'/'.$m->id, $this->mantenimientoDatos())->assertNotFound();
        $this->delete($base.'/'.$m->id)->assertNotFound();
        $this->get('/movilidad/drones-robots/999')->assertNotFound();
        $this->get('/movilidad/baterias/999')->assertNotFound();
        $this->get('/movilidad/drones-robots/999/mantenimientos')->assertNotFound();
        $this->postJson($base, $this->mantenimientoDatos(['mantenible_tipo' => 'App\\Models\\Usuario', 'mantenible_id' => 1]))->assertUnprocessable()->assertJsonValidationErrors(['mantenible_tipo', 'mantenible_id']);
        $this->assertDatabaseCount('mantenimientos', 1);
    }

    public function test_no_se_puede_borrar_historial_revirtiendo_un_trabajo_iniciado(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $m = $drone->mantenimientos()->create($this->mantenimientoDatos(['estado' => 'en_curso']));
        $base = '/movilidad/drones-robots/'.$drone->id.'/mantenimientos/'.$m->id;
        $this->putJson($base, $this->mantenimientoDatos(['estado' => 'programado']))->assertUnprocessable()->assertJsonValidationErrors('estado');
        $this->delete($base)->assertForbidden();
        $this->assertDatabaseHas('mantenimientos', ['id' => $m->id, 'estado' => 'en_curso']);
    }

    public function test_mantenimientos_rechazan_datos_invalidos(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $base = '/movilidad/drones-robots/'.$drone->id.'/mantenimientos';
        foreach ([['tipo' => 'otro'], ['descripcion' => ''], ['estado' => 'otro'], ['costo' => '-1'], ['costo' => '1.234'], ['responsable_id' => 999], ['fecha' => today()->subYears(2)->toDateString()], ['estado' => 'realizado', 'resultado' => null], ['estado' => 'en_curso', 'fecha' => today()->addDay()->toDateString()]] as $cambio) {
            $this->postJson($base, $this->mantenimientoDatos($cambio))->assertUnprocessable();
        }
        $this->assertDatabaseCount('mantenimientos', 0);
    }

    public function test_eliminacion_con_historial_y_baterias_se_bloquea(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        $bateria = Bateria::factory()->create(['dron_robot_id' => $drone->id]);
        $bateria->mantenimientos()->create($this->mantenimientoDatos(['estado' => 'realizado', 'resultado' => 'Correcto']));
        $this->deleteJson('/movilidad/drones-robots/'.$drone->id)->assertUnprocessable();
        $this->deleteJson('/movilidad/baterias/'.$bateria->id)->assertUnprocessable();
        $sinBateria = DroneRobot::factory()->create();
        $sinBateria->mantenimientos()->create($this->mantenimientoDatos());
        $this->deleteJson('/movilidad/drones-robots/'.$sinBateria->id)->assertUnprocessable();
        $this->assertDatabaseCount('baterias', 1);
        $this->assertDatabaseCount('mantenimientos', 2);
    }

    public function test_permiso_mantenimiento_no_da_acceso_a_recurso_sin_permiso(): void
    {
        $drone = DroneRobot::factory()->create();
        $bateria = Bateria::factory()->create(['dron_robot_id' => $drone->id]);
        $this->actingAs($this->usuario(['mantenimientos.ver', 'mantenimientos.crear', 'baterias.ver']));
        $this->get('/movilidad/drones-robots/'.$drone->id.'/mantenimientos')->assertForbidden();
        $this->post('/movilidad/drones-robots/'.$drone->id.'/mantenimientos', $this->mantenimientoDatos())->assertForbidden();
        $this->get('/movilidad/baterias/'.$bateria->id)->assertForbidden();
    }

    public function test_auditoria_fallida_revierte_alta_y_revision(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create();
        Auditoria::creating(fn () => throw new \RuntimeException('Auditoría no disponible'));
        try {
            $this->withoutExceptionHandling()->post('/movilidad/drones-robots/'.$drone->id.'/mantenimientos', $this->mantenimientoDatos());
            $this->fail('Debió fallar la auditoría.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Auditoría no disponible', $exception->getMessage());
            $this->assertDatabaseCount('mantenimientos', 0);
        } finally {
            Auditoria::flushEventListeners();
            Model::clearBootedModels();
        }
    }

    public function test_factories_y_seeder_son_funcionales_e_idempotentes(): void
    {
        MantenimientoMovilidad::factory()->create();
        $this->seed(MovilidadDemoSeeder::class);
        $conteos = [DroneRobot::count(), Bateria::count(), Mantenimiento::count()];
        $this->seed(MovilidadDemoSeeder::class);
        $this->assertSame($conteos, [DroneRobot::count(), Bateria::count(), Mantenimiento::count()]);
    }

    public function test_xss_se_escapa_en_listado_y_detalle(): void
    {
        $this->actingAs($this->usuario());
        $drone = DroneRobot::factory()->create(['nombre' => '<script>alert(1)</script>']);
        $this->get('/movilidad/drones-robots')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
        $this->get('/movilidad/drones-robots/'.$drone->id)->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_csrf_es_obligatorio_fuera_del_entorno_de_pruebas(): void
    {
        $this->actingAs($this->usuario());
        $this->app['env'] = 'local';
        $this->post('/movilidad/drones-robots', $this->droneDatos())->assertStatus(419);
        $this->withSession(['_token' => 'token-de-prueba'])->post('/movilidad/drones-robots', $this->droneDatos(['_token' => 'token-de-prueba']))->assertRedirect();
        $this->assertDatabaseCount('drones_robots', 1);
    }

    public function test_fallo_al_auditar_revision_revierte_mantenimiento_y_bateria(): void
    {
        $this->actingAs($this->usuario());
        $bateria = Bateria::factory()->create();
        Auditoria::creating(function (Auditoria $auditoria) {
            if ($auditoria->tabla_afectada === 'baterias') {
                throw new \RuntimeException('Fallo en revisión');
            }
        });
        try {
            $this->withoutExceptionHandling()->post('/movilidad/baterias/'.$bateria->id.'/mantenimientos', $this->mantenimientoDatos(['estado' => 'realizado', 'resultado' => 'Correcto']));
            $this->fail('Debió fallar la revisión.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo en revisión', $exception->getMessage());
            $this->assertDatabaseCount('mantenimientos', 0);
            $this->assertNull($bateria->fresh()->ultima_revision);
        } finally {
            Auditoria::flushEventListeners();
            Model::clearBootedModels();
        }
    }
}
