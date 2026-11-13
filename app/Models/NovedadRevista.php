<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NovedadRevista extends Model
{
    use RegistraAuditoria;

    protected $table = 'novedades_revista';

    public const SITUACIONES = [
        'activo' => 'Activo',
        'licencia' => 'Licencia',
        'carpeta_medica' => 'Carpeta médica',
        'franco_servicio' => 'Franco de servicio',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'policia_id',
        'situacion',
        'fecha_desde',
        'fecha_hasta',
        'licencia_id',
        'observaciones',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
        ];
    }

    public function getSituacionTextoAttribute(): string
    {
        return self::SITUACIONES[$this->situacion] ?? $this->situacion;
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }

    public function licencia(): BelongsTo
    {
        return $this->belongsTo(Licencia::class, 'licencia_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopeVigentesAl(Builder $query, CarbonInterface $fecha): Builder
    {
        return $query->whereDate('fecha_desde', '<=', $fecha)
            ->where(fn ($q) => $q->whereNull('fecha_hasta')->orWhereDate('fecha_hasta', '>=', $fecha));
    }
}
