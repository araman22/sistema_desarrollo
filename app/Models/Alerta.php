<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alerta extends Model
{
    use RegistraAuditoria;

    protected $table = 'alertas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo',
        'titulo',
        'descripcion',
        'alertable_id',
        'alertable_tipo',
        'usuario_id',
        'prioridad',
        'fecha_generacion',
        'fecha_vencimiento',
        'estado',
        'fecha_lectura',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_generacion' => 'datetime',
            'fecha_vencimiento' => 'datetime',
            'fecha_lectura' => 'datetime',
        ];
    }

    /**
     * Registro que origina la alerta (relacion polimorfica):
     * Vehiculo, DroneRobot, EquipamientoTecnologico, IncidenciaInfraestructura...
     */
    public function alertable(): MorphTo
    {
        return $this->morphTo('alertable', 'alertable_tipo', 'alertable_id');
    }

    /**
     * Usuario destinatario de la alerta.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now())
            ->where('estado', '!=', 'atendida');
    }

    public function scopeDeUsuario(Builder $query, int $usuarioId): Builder
    {
        return $query->where('usuario_id', $usuarioId);
    }
}
