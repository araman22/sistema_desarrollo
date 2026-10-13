<?php

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Intentos fallidos permitidos por nombre_usuario + IP.
     */
    private const MAX_INTENTOS = 5;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre_usuario' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_usuario.required' => 'Ingresá tu nombre de usuario.',
            'nombre_usuario.string' => 'El nombre de usuario no es válido.',
            'nombre_usuario.max' => 'El nombre de usuario no puede superar los 100 caracteres.',
            'password.required' => 'Ingresá tu contraseña.',
            'password.string' => 'La contraseña no es válida.',
            'remember.boolean' => 'El valor de "recordarme" no es válido.',
        ];
    }

    /**
     * Intenta iniciar sesión. Solo entra si las credenciales son correctas
     * y el usuario está activo.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->asegurarQueNoEsteBloqueado();

        // attemptWhen solo ejecuta el callback si las credenciales son
        // correctas; así se distingue un usuario inactivo de un error de clave.
        $inactivo = false;

        $autenticado = Auth::attemptWhen(
            $this->only('nombre_usuario', 'password'),
            function (Usuario $usuario) use (&$inactivo) {
                $inactivo = ! $usuario->estaActivo();

                return ! $inactivo;
            },
            $this->boolean('remember'),
        );

        if ($inactivo) {
            throw ValidationException::withMessages([
                'nombre_usuario' => 'Tu usuario está desactivado. Contactá al administrador.',
            ]);
        }

        if (! $autenticado) {
            RateLimiter::hit($this->claveLimite());

            throw ValidationException::withMessages([
                'nombre_usuario' => 'Usuario o contraseña incorrectos.',
            ]);
        }

        RateLimiter::clear($this->claveLimite());
    }

    /**
     * @throws ValidationException
     */
    private function asegurarQueNoEsteBloqueado(): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveLimite(), self::MAX_INTENTOS)) {
            return;
        }

        $segundos = RateLimiter::availableIn($this->claveLimite());

        throw ValidationException::withMessages([
            'nombre_usuario' => "Demasiados intentos fallidos. Esperá {$segundos} segundos antes de volver a intentar.",
        ]);
    }

    private function claveLimite(): string
    {
        return Str::transliterate(Str::lower($this->string('nombre_usuario')).'|'.$this->ip());
    }
}
