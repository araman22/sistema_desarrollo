<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra la sesión de un usuario que fue desactivado mientras estaba logueado.
 */
class AsegurarUsuarioActivo
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->estaActivo()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'nombre_usuario' => 'Tu usuario fue desactivado. Contactá al administrador.',
            ]);
        }

        return $next($request);
    }
}
