<?php

namespace App\Models;

use App\Models\Concerns\AuditaMovilidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Validation\ValidationException;

class Bateria extends Model
{
    use AuditaMovilidad, HasFactory;

    public const ESTADOS = ['disponible', 'en_uso', 'en_revision', 'agotada', 'baja'];

    protected static function booted(): void
    {
        static::deleting(function (self $recurso) {
            if ($recurso->mantenimientos()->exists()) {
                throw ValidationException::withMessages(['recurso' => 'No se puede eliminar una batería con historial. Podés darla de baja.']);
            }
        });
    }

    public function mantenimientos(): MorphMany
    {
        return $this->morphMany(MantenimientoMovilidad::class, 'mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    protected $table = 'baterias';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dron_robot_id',
        'codigo',
        'marca',
        'modelo',
        'numero_serie',
        'capacidad',
        'ciclos',
        'estado',
        'fecha_adquisicion',
        'ultima_revision',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ciclos' => 'integer',
            'fecha_adquisicion' => 'date',
            'ultima_revision' => 'date',
        ];
    }

    public function droneRobot(): BelongsTo
    {
        return $this->belongsTo(DroneRobot::class, 'dron_robot_id');
    }
}
