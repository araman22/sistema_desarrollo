<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capacitacion extends Model
{
    use RegistraAuditoria;

    protected $table = 'capacitaciones';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'institucion',
        'fecha_inicio',
        'fecha_fin',
        'cantidad_horas',
        'certificado',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'cantidad_horas' => 'decimal:2',
            'certificado' => 'boolean',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(CapacitacionPersonal::class, 'capacitacion_id');
    }

    public function policias(): BelongsToMany
    {
        return $this->belongsToMany(Policia::class, 'capacitacion_personal', 'capacitacion_id', 'policia_id');
    }
}
