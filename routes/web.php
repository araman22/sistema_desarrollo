<?php

use App\Http\Controllers\CajaChicaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EquipamientoTecnologicoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\TipoDocumentoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/caja-chica', [CajaChicaController::class, 'index'])->name('caja-chica.index');
Route::post('/caja-chica', [CajaChicaController::class, 'store'])->name('caja-chica.store');
Route::get('/caja-chica/{cajaChica}/ticket', [CajaChicaController::class, 'downloadTicket'])->name('caja-chica.ticket');

Route::resource('oficinas', OficinaController::class)
    ->parameters(['oficinas' => 'oficina']);

Route::resource('inventarios', InventarioController::class)
    ->parameters(['inventarios' => 'inventario']);

Route::resource('equipamientos-tecnologicos', EquipamientoTecnologicoController::class)
    ->parameters(['equipamientos-tecnologicos' => 'equipamientoTecnologico']);

Route::resource('tipo-documentos', TipoDocumentoController::class)
    ->parameters(['tipo-documentos' => 'tipoDocumento']);

Route::resource('documentos', DocumentoController::class)
    ->parameters(['documentos' => 'documento']);

Route::get('/documentos/{documento}/download', [DocumentoController::class, 'download'])
    ->name('documentos.download');

