<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CajaChicaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EquipamientoTecnologicoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\TipoDocumentoController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/caja-chica', [CajaChicaController::class, 'index'])->name('caja-chica.index');
    Route::post('/caja-chica', [CajaChicaController::class, 'store'])->name('caja-chica.store');
    Route::get('/caja-chica/{cajaChica}/ticket', [CajaChicaController::class, 'downloadTicket'])->name('caja-chica.ticket');

    Route::resource('oficinas', OficinaController::class)
        ->parameters(['oficinas' => 'oficina'])
        ->middleware(['can:viewAny,App\\Models\\Oficina']);

    Route::resource('inventarios', InventarioController::class)
        ->parameters(['inventarios' => 'inventario'])
        ->middleware(['can:viewAny,App\\Models\\Inventario']);

    Route::resource('equipamientos-tecnologicos', EquipamientoTecnologicoController::class)
        ->parameters(['equipamientos-tecnologicos' => 'equipamientoTecnologico'])
        ->middleware(['can:viewAny,App\\Models\\EquipamientoTecnologico']);

    Route::resource('tipo-documentos', TipoDocumentoController::class)
        ->parameters(['tipo-documentos' => 'tipoDocumento'])
        ->middleware(['can:viewAny,App\\Models\\TipoDocumento']);

    Route::resource('documentos', DocumentoController::class)
        ->parameters(['documentos' => 'documento'])
        ->middleware(['can:viewAny,App\\Models\\Documento']);

    Route::get('/documentos/{documento}/download', [DocumentoController::class, 'download'])
        ->name('documentos.download')
        ->middleware(['can:download,documento']);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


Route::get('/login', function () {
    return view('auth.login');
})->name('login');
