<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AccesoMovilidad
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            if (! $request->expectsJson() && Route::has('login')) {
                return redirect()->guest(route('login'));
            }
            abort(401, 'Debés iniciar sesión con la autenticación del sistema.');
        }
        abort_unless($request->user()->estaActivo() && $request->user()->rol?->activo, 403);

        return $next($request);
    }
}
