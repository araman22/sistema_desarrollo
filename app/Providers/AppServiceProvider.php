<?php

namespace App\Providers;

use App\Models\Documento;
use App\Models\EquipamientoTecnologico;
use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\TipoDocumento;
use App\Policies\DocumentoPolicy;
use App\Policies\EquipamientoTecnologicoPolicy;
use App\Policies\InventarioPolicy;
use App\Policies\OficinaPolicy;
use App\Policies\TipoDocumentoPolicy;
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
        Gate::policy(Oficina::class, OficinaPolicy::class);
        Gate::policy(Inventario::class, InventarioPolicy::class);
        Gate::policy(EquipamientoTecnologico::class, EquipamientoTecnologicoPolicy::class);
        Gate::policy(TipoDocumento::class, TipoDocumentoPolicy::class);
        Gate::policy(Documento::class, DocumentoPolicy::class);
    }
}
