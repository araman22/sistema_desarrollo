<?php

namespace App\Models;

use App\Models\Concerns\AuditaMovilidad;
use Database\Factories\MantenimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** Misma tabla, auditoría obligatoria sin alterar los mantenimientos ajenos. */
class MantenimientoMovilidad extends Mantenimiento
{
    use AuditaMovilidad, HasFactory;

    protected $table = 'mantenimientos';

    protected static function newFactory(): MantenimientoFactory
    {
        return MantenimientoFactory::new();
    }
}
