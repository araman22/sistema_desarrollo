<?php

namespace App\Providers;

use App\Models\Bateria;
use App\Models\DroneRobot;
use App\Policies\BateriaPolicy;
use App\Policies\DroneRobotPolicy;
use App\Policies\MantenimientoMovilidadPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class MovilidadServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(DroneRobot::class, DroneRobotPolicy::class);
        Gate::policy(Bateria::class, BateriaPolicy::class);
        // Abilities propias: no reemplazan la Policy del mantenimiento general de otros grupos.
        foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $accion) {
            Gate::define('movilidad.mantenimiento.'.$accion, [MantenimientoMovilidadPolicy::class, $accion]);
        }
    }
}
