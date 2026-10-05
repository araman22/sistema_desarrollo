<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Licencia extends Model
{
    use RegistraAuditoria;

    protected $table = 'licencias';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'policia_id',
        'tipo_licencia_id',
        'fecha_inicio',
        'fecha_fin',
        'cantidad_dias',
        'estado',
        'motivo',
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
            'cantidad_dias' => 'integer',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoLicencia::class, 'tipo_licencia_id');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAutorizadas(Builder $query): Builder
    {
        return $query->where('estado', 'autorizada');
    }
}
