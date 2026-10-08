<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RecuperarContrasenaController;
use App\Http\Controllers\Auth\RestablecerContrasenaController;
use App\Http\Controllers\RolController;
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
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::resource('usuarios', UsuarioController::class)->parameters(['usuarios' => 'usuario']);
    Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');

    Route::resource('roles', RolController::class)->parameters(['roles' => 'rol']);
    Route::patch('roles/{rol}/estado', [RolController::class, 'cambiarEstado'])->name('roles.estado');
});
