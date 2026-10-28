<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RutasIntegracionTest extends TestCase
{
    public function test_los_enlaces_estaticos_de_las_vistas_tienen_ruta(): void
    {
        foreach (\Illuminate\Support\Facades\File::allFiles(resource_path('views')) as $file) {
            preg_match_all('/\broute\(\s*[\'"]([^\'"]+)[\'"]\s*[,)]/', $file->getContents(), $matches);
            foreach ($matches[1] as $name) {
                $this->assertTrue(Route::has($name), $file->getRelativePathname().': '.$name);
            }
        }
    }
    public function test_las_rutas_apuntan_a_controladores_y_metodos_existentes(): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if ($action === 'Closure') {
                continue;
            }
            [$controller, $method] = explode('@', $action);
            $this->assertTrue(class_exists($controller), $action);
            $this->assertTrue(method_exists($controller, $method), $action);
            $middleware = $route->gatherMiddleware();
            $this->assertFalse(in_array('auth', $middleware) && in_array('guest', $middleware), $route->uri());
        }
    }

    public function test_los_modulos_protegidos_redirigen_al_login(): void
    {
        foreach (['dashboard', 'oficinas.index', 'inventarios.index', 'documentos.index', 'fondo-fijo.index', 'vehiculos.index', 'movilidad.drones.index', 'movilidad.baterias.index'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }

    public function test_el_acceso_anterior_de_movilidad_redirige_al_login_general(): void
    {
        $this->get('/movilidad/acceso')->assertStatus(301)->assertRedirect(route('login'));
        $this->post('/movilidad/acceso', [])->assertStatus(405);
        $this->assertFalse(Route::has('movilidad.login'));
        $this->assertFalse(Route::has('movilidad.logout'));
    }

    public function test_la_navegacion_de_movilidad_y_los_documentos_de_vehiculos_existen(): void
    {
        $this->assertTrue(view()->exists('movilidad.partials.navigation'));
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, (new \App\Models\Vehiculo)->documentos());
    }
}