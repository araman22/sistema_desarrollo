<?php

namespace App\Models;

use App\Models\Concerns\AuditaMovilidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

class DroneRobot extends Model
{
    use AuditaMovilidad, HasFactory;

    public const TIPOS = ['dron', 'robot'];

    public const ESTADOS = ['operativo', 'en_mantenimiento', 'fuera_servicio', 'baja'];

    protected static function booted(): void
    {
        static::deleting(function (self $recurso) {
            if ($recurso->baterias()->exists() || $recurso->mantenimientos()->exists()) {
                throw ValidationException::withMessages(['recurso' => 'No se puede eliminar un recurso con baterías o historial. Podés darlo de baja.']);
            }
        });
    }

    protected $table = 'drones_robots';

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
        return $this->morphMany(MantenimientoMovilidad::class, 'mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
