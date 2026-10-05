<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Proyecto extends Model
{
    use RegistraAuditoria;

    protected $table = 'proyectos';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'objetivo',
        'oficina_id',
        'responsable_id',
        'fecha_inicio',
        'fecha_fin',
        'prioridad',
        'estado',
        'porcentaje_avance',
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
            'porcentaje_avance' => 'decimal:2',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function integrantes(): HasMany
    {
        return $this->hasMany(ProyectoIntegrante::class, 'proyecto_id');
    }

    public function policias(): BelongsToMany
    {
        return $this->belongsToMany(Policia::class, 'proyecto_integrantes', 'proyecto_id', 'policia_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoEconomico::class, 'proyecto_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function scopeEnCurso(Builder $query): Builder
    {
        return $query->where('estado', 'en_curso');
    }

    public function scopeFinalizados(Builder $query): Builder
    {
        return $query->where('estado', 'finalizado');
    }
}
