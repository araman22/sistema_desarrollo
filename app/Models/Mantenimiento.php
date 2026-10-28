<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Database\Factories\MantenimientoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Mantenimiento extends Model
{
    /** @use HasFactory<MantenimientoFactory> */
    use HasFactory, RegistraAuditoria;

    public const TIPOS_MOVILIDAD = ['preventivo', 'correctivo', 'revision'];

    public const ESTADOS_MOVILIDAD = ['programado', 'en_curso', 'realizado', 'cancelado'];

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

    // Los mantenimientos de drones y baterías no se guardan sin su auditoría; los demás solo la registran.
    protected function auditoriaObligatoria(): bool
    {
        return in_array($this->mantenible_tipo, [(new DroneRobot)->getMorphClass(), (new Bateria)->getMorphClass()], true);
    }

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
