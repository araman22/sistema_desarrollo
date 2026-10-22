<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoDocumentoController;
use App\Http\Controllers\VehiculoMantenimientoController;
use Illuminate\Support\Facades\Route;

// Las direcciones anteriores de acceso usan la autenticación general del sistema.
Route::middleware('guest')->group(function () {
    Route::get('/movilidad/acceso', [LoginController::class, 'create'])->name('movilidad.login');
    Route::post('/movilidad/acceso', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('movilidad.login.store');
});
Route::middleware('auth')->scopeBindings()->group(function () {
    Route::post('/movilidad/salir', [LoginController::class, 'destroy'])->name('movilidad.logout');
    Route::resource('vehiculos', VehiculoController::class);
    Route::resource('tipos-vehiculos', TipoVehiculoController::class)->parameters(['tipos-vehiculos' => 'tipo']);
    Route::resource('vehiculos.mantenimientos', VehiculoMantenimientoController::class)->except(['index', 'show'])->parameters(['mantenimientos' => 'mantenimiento']);
    Route::post('/vehiculos/{vehiculo}/documentos', [VehiculoDocumentoController::class, 'store'])->name('vehiculos.documentos.store');
    Route::get('/vehiculos/{vehiculo}/documentos/{documento}', [VehiculoDocumentoController::class, 'download'])->name('vehiculos.documentos.download');
});
