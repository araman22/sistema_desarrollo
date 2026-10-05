<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DroneRobot extends Model
{
    use RegistraAuditoria;

    protected $table = 'drones_robots';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo',
        'nombre',
        'codigo',
        'marca',
        'modelo',
        'numero_serie',
        'estado',
        'responsable_id',
        'fecha_adquisicion',
        'horas_uso',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'horas_uso' => 'decimal:2',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function baterias(): HasMany
    {
        return $this->hasMany(Bateria::class, 'dron_robot_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function mantenimientos(): MorphMany
    {
        return $this->morphMany(Mantenimiento::class, 'mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
