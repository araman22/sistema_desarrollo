<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Licencia extends Model
{
    use RegistraAuditoria;

    protected $table = 'licencias';

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'autorizada' => 'Autorizada',
        'rechazada' => 'Rechazada',
        'cancelada' => 'Cancelada',
    ];

    // Estados que reservan días del cupo anual y bloquean superposiciones.
    public const ESTADOS_VIGENTES = ['pendiente', 'autorizada'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'policia_id',
        'tipo_licencia_id',
        'anio_correspondiente',
        'fecha_inicio',
        'fecha_fin',
        'cantidad_dias',
        'fecha_regreso_efectivo',
        'estado',
        'motivo',
        'observaciones',
        'requiere_reemplazo',
        'reemplazo_id',
        'cobertura_desde',
        'cobertura_hasta',
        'cobertura_observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anio_correspondiente' => 'integer',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_regreso_prevista' => 'date',
            'fecha_regreso_efectivo' => 'date',
            'cantidad_dias' => 'integer',
            'requiere_reemplazo' => 'boolean',
            'cobertura_desde' => 'date',
            'cobertura_hasta' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Días corridos: el último día es salida + días - 1 y el regreso previsto, el día siguiente.
        static::saving(function (Licencia $licencia) {
            if ($licencia->fecha_inicio && $licencia->cantidad_dias) {
                $licencia->fecha_fin = $licencia->fecha_inicio->copy()->addDays($licencia->cantidad_dias - 1);
                $licencia->fecha_regreso_prevista = $licencia->fecha_inicio->copy()->addDays($licencia->cantidad_dias);
            }
            $licencia->anio_correspondiente ??= $licencia->fecha_inicio?->year;
        });
    }

    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoLicencia::class, 'tipo_licencia_id');
    }

    public function reemplazo(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'reemplazo_id');
    }

    public function novedad(): HasOne
    {
        return $this->hasOne(NovedadRevista::class, 'licencia_id');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAutorizadas(Builder $query): Builder
    {
        return $query->where('estado', 'autorizada');
    }

    public function scopeVigentes(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ESTADOS_VIGENTES);
    }

    public function scopeSuperpuestaCon(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereDate('fecha_inicio', '<=', $hasta)->whereDate('fecha_fin', '>=', $desde);
    }
}
