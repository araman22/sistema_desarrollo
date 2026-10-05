<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Arma extends Model
{
    use RegistraAuditoria;

    protected $table = 'armas';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * Arma fisica. El modelo (marca/modelo/calibre) esta en `armas_modelos`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'modelo_arma_id',
        'numero_serie',
        'estado',
        'fecha_adquisicion',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ArmaModelo::class, 'modelo_arma_id');
    }

    /**
     * Policia que tiene asignado este arma (arma_id en policias).
     */
    public function policia(): HasOne
    {
        return $this->hasOne(Policia::class, 'arma_id');
    }

    public function scopeDeModelo(Builder $query, string $marca): Builder
    {
        return $query->whereHas('modelo', fn (Builder $q) => $q->where('marca', $marca));
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->whereDoesntHave('policia');
    }
}
