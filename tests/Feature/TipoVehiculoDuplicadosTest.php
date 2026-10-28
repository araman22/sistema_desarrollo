<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\TipoVehiculo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TipoVehiculoDuplicadosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $rol = Rol::create(['nombre' => uniqid('rol-'), 'activo' => true]);
        foreach (['ver', 'crear', 'editar', 'eliminar'] as $accion) {
            $rol->permisos()->attach(Permiso::firstOrCreate(['nombre' => "vehiculos.$accion"], ['modulo' => 'vehiculos', 'accion' => $accion]));
        }
        $this->actingAs(Usuario::factory()->create(['rol_id' => $rol->id]));
    }

    public function test_rechaza_variantes_de_tildes_mayusculas_y_espacios_nombrando_el_existente(): void
    {
        TipoVehiculo::create(['nombre' => 'Camión']);

        foreach (['Camion', 'CAMIÓN', '  camion  ', 'cámión'] as $variante) {
            $this->from(route('tipos-vehiculos.create'))
                ->post(route('tipos-vehiculos.store'), ['nombre' => $variante])
                ->assertRedirect(route('tipos-vehiculos.create'))
                ->assertSessionHasErrors(['nombre' => 'Ya existe el tipo de vehículo «Camión».'])
                ->assertSessionHasInput('nombre');
        }
        $this->assertDatabaseCount('tipo_vehiculos', 1);
    }

    public function test_conserva_la_escritura_original_y_quita_espacios_sobrantes(): void
    {
        $this->post(route('tipos-vehiculos.store'), ['nombre' => '  Camión   Grúa  '])->assertRedirect(route('tipos-vehiculos.index'));

        $this->assertDatabaseHas('tipo_vehiculos', ['nombre' => 'Camión Grúa', 'nombre_normalizado' => 'camion grua']);
    }

    public function test_la_enie_no_se_confunde_con_la_n(): void
    {
        $this->post(route('tipos-vehiculos.store'), ['nombre' => 'Caña'])->assertSessionHasNoErrors();
        $this->post(route('tipos-vehiculos.store'), ['nombre' => 'Cana'])->assertSessionHasNoErrors();
        $this->post(route('tipos-vehiculos.store'), ['nombre' => 'CAÑA'])->assertSessionHasErrors(['nombre' => 'Ya existe el tipo de vehículo «Caña».']);

        $this->assertDatabaseCount('tipo_vehiculos', 2);
    }

    public function test_edicion_permite_corregir_el_propio_nombre_pero_no_chocar_con_otro(): void
    {
        $moto = TipoVehiculo::create(['nombre' => 'moto']);
        TipoVehiculo::create(['nombre' => 'Camión']);

        $this->put(route('tipos-vehiculos.update', $moto), ['nombre' => 'Moto'])->assertSessionHasNoErrors();
        $this->assertSame('Moto', $moto->fresh()->nombre);

        $this->put(route('tipos-vehiculos.update', $moto), ['nombre' => 'camion'])->assertSessionHasErrors(['nombre' => 'Ya existe el tipo de vehículo «Camión».']);
        $this->assertSame('Moto', $moto->fresh()->nombre);
    }

    public function test_alta_simultanea_que_paso_la_validacion_devuelve_mensaje_y_no_error(): void
    {
        // Simula otra petición que guarda el mismo tipo entre la validación y el insert.
        TipoVehiculo::creating(function () {
            DB::table('tipo_vehiculos')->insertOrIgnore(['nombre' => 'Camión', 'nombre_normalizado' => 'camion', 'created_at' => now(), 'updated_at' => now()]);
        });

        // En la prueba la fila "ajena" se revierte con la transacción; en producción queda confirmada.
        $this->from(route('tipos-vehiculos.create'))
            ->post(route('tipos-vehiculos.store'), ['nombre' => 'Camion'])
            ->assertRedirect(route('tipos-vehiculos.create'))
            ->assertSessionHasErrors('nombre')
            ->assertSessionHasInput('nombre', 'Camion');
    }

    public function test_unificacion_conserva_vehiculos_e_historial(): void
    {
        // Datos anteriores a la validación: se quita el índice para poder cargar duplicados.
        Schema::table('tipo_vehiculos', fn (Blueprint $t) => $t->dropUnique(['nombre_normalizado']));
        $original = TipoVehiculo::create(['nombre' => 'Camión']);
        $duplicado = TipoVehiculo::create(['nombre' => 'CAMION']);
        TipoVehiculo::create(['nombre' => 'Caña']);
        TipoVehiculo::create(['nombre' => 'Cana']);
        $vehiculo = Vehiculo::factory()->create(['tipo_vehiculo_id' => $duplicado->id, 'kilometraje' => 1000]);
        $vehiculo->mantenimientos()->create(['tipo' => 'preventivo', 'descripcion' => 'Service', 'fecha' => today(), 'kilometraje' => 1000]);

        $this->artisan('tipos-vehiculos:unificar')->expectsOutputToContain('Revisión solamente')->assertSuccessful();
        $this->assertDatabaseHas('tipo_vehiculos', ['id' => $duplicado->id]);

        $this->artisan('tipos-vehiculos:unificar', ['--aplicar' => true])->expectsOutputToContain('1 grupo(s) unificado(s)')->assertSuccessful();

        $this->assertDatabaseMissing('tipo_vehiculos', ['id' => $duplicado->id]);
        $this->assertDatabaseHas('tipo_vehiculos', ['id' => $original->id, 'nombre' => 'Camión']);
        $this->assertSame($original->id, $vehiculo->fresh()->tipo_vehiculo_id);
        $this->assertSame(1, $vehiculo->mantenimientos()->count());
        $this->assertDatabaseHas('movilidad_auditorias', ['accion' => 'unificar_tipo', 'tabla' => 'vehiculos', 'registro_id' => $vehiculo->id]);
        $this->assertDatabaseHas('tipo_vehiculos', ['nombre' => 'Caña']);
        $this->assertDatabaseHas('tipo_vehiculos', ['nombre' => 'Cana']);
    }
}
