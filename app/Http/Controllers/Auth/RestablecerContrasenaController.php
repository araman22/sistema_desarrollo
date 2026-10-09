<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as ReglaContrasena;
use Illuminate\View\View;

class RestablecerContrasenaController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.restablecer-contrasena', [
            'token' => $token,
            'correo' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'correo_electronico' => ['required', 'email'],
            'password' => ['required', 'confirmed', ReglaContrasena::min(8)->letters()->numbers()],
        ], [
            'token.required' => 'El enlace no es válido. Pedí uno nuevo.',
            'correo_electronico.required' => 'Ingresá tu correo electrónico.',
            'correo_electronico.email' => 'Ingresá un correo electrónico válido.',
            'password.required' => 'Ingresá la nueva contraseña.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.password.letters' => 'La contraseña debe tener al menos una letra.',
            'password.password.numbers' => 'La contraseña debe tener al menos un número.',
        ]);

        $estado = Password::reset(
            $request->only('correo_electronico', 'password', 'password_confirmation', 'token'),
            function (Usuario $usuario, string $password) {
                $usuario->forceFill([
                    'contrasena' => $password,
                    'recordar_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));
            },
        );

        if ($estado === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Tu contraseña fue restablecida. Ya podés ingresar.');
        }

        return back()
            ->withInput($request->only('correo_electronico'))
            ->withErrors(['correo_electronico' => 'El enlace para restablecer la contraseña no es válido o ya venció. Pedí uno nuevo.']);
    }
}
