<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Oficina extends Model
{
    use RegistraAuditoria;

    protected $table = 'oficinas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'telefono',
        'correo_electronico',
        'responsable_id',
        'estado',
        'observaciones',
    ];

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function policias(): HasMany
    {
        return $this->hasMany(Policia::class, 'oficina_id');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'oficina_id');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'oficina_id');
    }

    public function equipamiento(): HasMany
    {
        return $this->hasMany(EquipamientoTecnologico::class, 'oficina_id');
    }

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'oficina_id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(IncidenciaInfraestructura::class, 'oficina_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', 'activa');
    }
}
