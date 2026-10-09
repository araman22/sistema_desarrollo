<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class RecuperarContrasenaController extends Controller
{
    public function create(): View
    {
        return view('auth.recuperar-contrasena');
    }

    /**
     * Siempre responde lo mismo para no revelar si el correo existe o si el
     * usuario está inactivo.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'correo_electronico' => ['required', 'email'],
        ], [
            'correo_electronico.required' => 'Ingresá tu correo electrónico.',
            'correo_electronico.email' => 'Ingresá un correo electrónico válido.',
        ]);

        $correo = $request->string('correo_electronico')->toString();

        $usuario = Usuario::where('correo_electronico', $correo)->first();

        if ($usuario?->estaActivo()) {
            $estado = Password::sendResetLink(['correo_electronico' => $correo]);

            if ($estado === Password::RESET_THROTTLED) {
                return back()
                    ->withInput($request->only('correo_electronico'))
                    ->withErrors(['correo_electronico' => 'Esperá un momento antes de pedir otro enlace.']);
            }
        }

        return back()->with('status', 'Si el correo está registrado, vas a recibir un enlace para restablecer tu contraseña.');
    }
}
