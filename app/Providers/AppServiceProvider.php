<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Las abilities "modulo.accion" se resuelven con los permisos del rol.
        // Devolver null (y no false) deja decidir a las Policies que se agreguen.
        Gate::before(function (Usuario $usuario, string $ability) {
            if (str_contains($ability, '.') && $usuario->tienePermiso($ability)) {
                return true;
            }

            return null;
        });

        ResetPassword::toMailUsing(function (Usuario $usuario, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $usuario->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Restablecer contraseña - SIGDET')
                ->greeting("Hola, {$usuario->nombre_usuario}")
                ->line('Recibimos un pedido para restablecer la contraseña de tu cuenta.')
                ->action('Restablecer contraseña', $url)
                ->line('Este enlace vence en '.config('auth.passwords.users.expire').' minutos.')
                ->line('Si no pediste restablecer la contraseña, podés ignorar este correo.')
                ->salutation('SIGDET');
        });
    }
}
