<?php

use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoDocumentoController;
use App\Http\Controllers\VehiculoMantenimientoController;
use App\Http\Middleware\AccesoMovilidad;
use Illuminate\Support\Facades\Route;

// La dirección anterior de acceso queda solo como redirección al login general.
Route::get('/movilidad/acceso', fn () => redirect()->route('login', [], 301));
Route::middleware(AccesoMovilidad::class)->scopeBindings()->group(function () {
    Route::resource('vehiculos', VehiculoController::class);
    Route::resource('tipos-vehiculos', TipoVehiculoController::class)->parameters(['tipos-vehiculos' => 'tipo']);
    Route::resource('vehiculos.mantenimientos', VehiculoMantenimientoController::class)->except(['index', 'show'])->parameters(['mantenimientos' => 'mantenimiento']);
    Route::post('/vehiculos/{vehiculo}/documentos', [VehiculoDocumentoController::class, 'store'])->name('vehiculos.documentos.store');
    Route::get('/vehiculos/{vehiculo}/documentos/{documento}', [VehiculoDocumentoController::class, 'download'])->name('vehiculos.documentos.download');
});
