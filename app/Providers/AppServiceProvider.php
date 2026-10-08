<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Las abilities "modulo.accion" se resuelven con los permisos del rol.
        // Devolver null (y no false) deja decidir a las Policies que se agreguen.
        Gate::before(function (Usuario $usuario, string $ability) {
            if (str_contains($ability, '.') && $usuario->tienePermiso($ability)) {
                return true;
            }

            return null;
        });
    }
}
