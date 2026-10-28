<?php

namespace Tests\Feature;

use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\PermisoSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioTotalesTest extends TestCase
{
    use RefreshDatabase;

    private Oficina $central;

    private Oficina $norte;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermisoSeeder::class, RolSeeder::class]);
        $this->actingAs(Usuario::factory()->create(['rol_id' => Rol::where('nombre', Rol::ADMINISTRADOR)->value('id')]));

        $this->central = Oficina::create(['nombre' => 'Central', 'estado' => 'activa']);
        $this->norte = Oficina::create(['nombre' => 'Norte', 'estado' => 'activa']);

        // 12 registros en Central (más que una página de 10) para comprobar que los totales no dependen de la paginación.
        foreach (range(1, 12) as $i) {
            $this->recurso("C-$i", $this->central, $i <= 8 ? 'Tecnologia' : 'Mobiliario', 2);
        }
        $this->recurso('N-1', $this->norte, 'Tecnologia', 5);
        $this->recurso('N-2', $this->norte, 'Mobiliario', 1);
        $this->recurso('S-1', null, 'Tecnologia', 3);
    }

    private function recurso(string $codigo, ?Oficina $oficina, string $categoria, int $cantidad): Inventario
    {
        return Inventario::create(['codigo' => $codigo, 'descripcion' => "Recurso $codigo", 'categoria' => $categoria, 'cantidad' => $cantidad, 'estado' => 'disponible', 'oficina_id' => $oficina?->id]);
    }

    public function test_todas_las_oficinas_y_categorias_devuelve_subtotales_y_total_de_todos_los_registros(): void
    {
        $respuesta = $this->get(route('inventarios.index'))->assertOk();

        $this->assertSame([
            ['oficina_id' => $this->central->id, 'oficina' => 'Central', 'registros' => 12, 'unidades' => 24],
            ['oficina_id' => $this->norte->id, 'oficina' => 'Norte', 'registros' => 2, 'unidades' => 6],
            ['oficina_id' => null, 'oficina' => 'Sin oficina', 'registros' => 1, 'unidades' => 3],
        ], $respuesta->viewData('subtotales')->all());
        $this->assertSame(['registros' => 15, 'unidades' => 33], $respuesta->viewData('totalGeneral'));
        $this->assertTrue($respuesta->viewData('agrupado'));
        $this->assertCount(10, $respuesta->viewData('inventarios'));
    }

    public function test_con_todas_las_oficinas_el_listado_sale_agrupado_por_oficina(): void
    {
        $oficinas = collect(range(1, 2))
            ->flatMap(fn ($pagina) => $this->get(route('inventarios.index', ['page' => $pagina]))->viewData('inventarios')->pluck('oficina_id'))
            ->values();

        $this->assertSame([...array_fill(0, 12, $this->central->id), $this->norte->id, $this->norte->id, null], $oficinas->all());
        $this->get(route('inventarios.index'))->assertSeeInOrder(['Central · Subtotal: 24 unidades', 'C-1']);
    }

    public function test_filtros_de_oficina_y_categoria_se_combinan(): void
    {
        $respuesta = $this->get(route('inventarios.index', ['oficina_id' => $this->central->id, 'categoria' => 'Tecnologia']));

        $this->assertSame(['registros' => 8, 'unidades' => 16], $respuesta->viewData('totalGeneral'));
        $this->assertFalse($respuesta->viewData('agrupado'));
    }

    public function test_categoria_con_todas_las_oficinas(): void
    {
        $respuesta = $this->get(route('inventarios.index', ['categoria' => 'Tecnologia']));

        $this->assertSame([16, 5, 3], $respuesta->viewData('subtotales')->pluck('unidades')->all());
        $this->assertSame(['registros' => 10, 'unidades' => 24], $respuesta->viewData('totalGeneral'));
    }

    public function test_oficina_con_todas_las_categorias(): void
    {
        $respuesta = $this->get(route('inventarios.index', ['oficina_id' => $this->norte->id, 'categoria' => '']));

        $this->assertSame(['registros' => 2, 'unidades' => 6], $respuesta->viewData('totalGeneral'));
    }

    public function test_la_cantidad_se_valida_y_se_guarda(): void
    {
        $datos = ['codigo' => 'L-1', 'descripcion' => 'Sillas', 'categoria' => 'Mobiliario', 'estado' => 'disponible', 'oficina_id' => $this->norte->id];

        $this->post(route('inventarios.store'), $datos + ['cantidad' => 0])->assertSessionHasErrors('cantidad');
        $this->post(route('inventarios.store'), $datos + ['cantidad' => 25])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inventarios', ['codigo' => 'L-1', 'cantidad' => 25]);
    }
}
