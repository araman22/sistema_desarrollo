<?php

use App\Http\Controllers\Movilidad\BateriaController;
use App\Http\Controllers\Movilidad\DroneRobotController;
use App\Http\Controllers\Movilidad\MantenimientoController;
use App\Http\Middleware\AccesoMovilidad;
use App\Models\Bateria;
use App\Models\DroneRobot;
use Illuminate\Support\Facades\Route;

Route::model('drone', DroneRobot::class);
Route::model('bateria', Bateria::class);
Route::prefix('movilidad')->name('movilidad.')->middleware(AccesoMovilidad::class)->group(function () {
    Route::get('/', fn () => redirect()->route(auth()->user()->can('viewAny', DroneRobot::class) ? 'movilidad.drones.index' : 'movilidad.baterias.index'))->name('index');
    Route::resource('drones-robots', DroneRobotController::class)->parameters(['drones-robots' => 'drone'])->names('drones');
    Route::resource('baterias', BateriaController::class)->parameters(['baterias' => 'bateria']);
    // El ID del mantenimiento se resuelve dentro de la relación del padre.
    foreach (['drones-robots/{drone}' => 'drones', 'baterias/{bateria}' => 'baterias'] as $ruta => $nombre) {
        Route::prefix($ruta)->name($nombre.'.')->group(function () {
            Route::resource('mantenimientos', MantenimientoController::class)->parameters(['mantenimientos' => 'mantenimiento']);
        });
    }
});
