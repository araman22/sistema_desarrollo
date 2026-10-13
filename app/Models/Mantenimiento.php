<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mantenimiento extends Model
{
    use RegistraAuditoria;

    protected $table = 'mantenimientos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'mantenible_id',
        'mantenible_tipo',
        'tipo',
        'descripcion',
        'fecha',
        'responsable_id',
        'costo',
        'estado',
        'resultado',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'costo' => 'decimal:2',
        ];
    }

    /**
     * Recurso sobre el que se realizo el mantenimiento (relacion polimorfica):
     * Inventario, EquipamientoTecnologico, DroneRobot, Vehiculo, Policia...
     */
    public function mantenible(): MorphTo
    {
        return $this->morphTo('mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
