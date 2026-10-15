<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsablePolicial extends Model
{
    protected $table = 'policias';

    protected $guarded = ['*'];

    public static function opciones()
    {
        return static::query()->join('personas', 'personas.id', '=', 'policias.persona_id')
            ->where('policias.estado', 'activo')->orderBy('personas.apellido')
            ->get(['policias.id', 'personas.nombre', 'personas.apellido', 'policias.numero_legajo']);
    }
}
