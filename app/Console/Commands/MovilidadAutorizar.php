<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MovilidadAutorizar extends Command
{
    protected $signature = 'movilidad:autorizar {email} {--editar} {--revocar}';

    protected $description = 'Asigna acceso local explícito al módulo; no crea usuarios ni contraseñas.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('No existe ese usuario.');

            return self::FAILURE;
        }
        if ($this->option('revocar')) {
            DB::table('movilidad_accesos')->where('user_id', $user->id)->delete();
        } else {
            DB::table('movilidad_accesos')->updateOrInsert(['user_id' => $user->id], ['puede_editar' => $this->option('editar'), 'created_at' => now(), 'updated_at' => now()]);
        }
        $this->info('Permisos actualizados.');

        return self::SUCCESS;
    }
}
