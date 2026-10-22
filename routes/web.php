<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RecuperarContrasenaController;
use App\Http\Controllers\Auth\RestablecerContrasenaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EquipamientoTecnologicoController;
use App\Http\Controllers\FondoFijoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    // Los nombres password.* los usa la notificación ResetPassword de Laravel.
    Route::get('/olvide-contrasena', [RecuperarContrasenaController::class, 'create'])->name('password.request');
    Route::post('/olvide-contrasena', [RecuperarContrasenaController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [RestablecerContrasenaController::class, 'create'])->name('password.reset');
    Route::post('/restablecer-contrasena', [RestablecerContrasenaController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Seguridad
    Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario']);
    Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');

    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol']);
    Route::patch('roles/{rol}/estado', [RolController::class, 'cambiarEstado'])->name('roles.estado');

    // Fondo fijo
    Route::get('/fondo-fijo', [FondoFijoController::class, 'index'])
        ->middleware('can:viewAny,App\Models\FondoFijoMovimiento')
        ->name('fondo-fijo.index');

    Route::post('/fondo-fijo', [FondoFijoController::class, 'store'])
        ->middleware('can:create,App\Models\FondoFijoMovimiento')
        ->name('fondo-fijo.store');

    Route::get('/fondo-fijo/{fondoFijo}/ticket', [FondoFijoController::class, 'downloadTicket'])
        ->middleware('can:download,fondoFijo')
        ->name('fondo-fijo.ticket');

    // Oficinas, inventario y documentación
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

});

// Cada archivo de movilidad define sus propios controles de acceso.
require __DIR__.'/movilidad.php';
require __DIR__.'/movilidad-recursos.php';
