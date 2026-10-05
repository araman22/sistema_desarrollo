<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Policia extends Model
{
    use RegistraAuditoria;

    protected $table = 'policias';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * Ficha laboral. La identidad (nombre, dni, contacto) esta en `persona`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'persona_id',
        'jerarquia_id',
        'arma_id',
        'oficina_id',
        'numero_legajo',
        'funcion_id',
        'fecha_ingreso',
        'estado',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    /**
     * Atributo derivado para no tener que recorrer persona en cada listado.
     */
    public function getNombreCompletoAttribute(): string
    {
        return $this->persona?->nombre_completo ?? '';
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function jerarquia(): BelongsTo
    {
        return $this->belongsTo(Jerarquia::class, 'jerarquia_id');
    }

    public function funcion(): BelongsTo
    {
        return $this->belongsTo(Funcion::class, 'funcion_id');
    }

    public function arma(): BelongsTo
    {
        return $this->belongsTo(Arma::class, 'arma_id');
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    /**
     * Usuario de acceso asociado a este police.
     */
    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'policia_id');
    }

    /**
     * Oficinas de las que este police es responsable.
     */
    public function oficinasResponsable(): HasMany
    {
        return $this->hasMany(Oficina::class, 'responsable_id');
    }

    public function licencias(): HasMany
    {
        return $this->hasMany(Licencia::class, 'policia_id');
    }

    public function capacitaciones(): HasMany
    {
        return $this->hasMany(CapacitacionPersonal::class, 'policia_id');
    }

    public function inventarios(): HasMany
    {
        return $this->hasMany(Inventario::class, 'responsable_id');
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'responsable_id');
    }

    public function mantenimientosVehiculo(): HasMany
    {
        return $this->hasMany(VehiculoMantenimiento::class, 'responsable_id');
    }

    public function dronesRobots(): HasMany
    {
        return $this->hasMany(DroneRobot::class, 'responsable_id');
    }

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'responsable_id');
    }

    public function proyectosIntegrante(): HasMany
    {
        return $this->hasMany(ProyectoIntegrante::class, 'policia_id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(IncidenciaInfraestructura::class, 'responsable_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoEconomico::class, 'responsable_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'responsable_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function mantenimientosRegistrados(): MorphMany
    {
        return $this->morphMany(Mantenimiento::class, 'mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    public function scopeDeJerarquia(Builder $query, int $jerarquiaId): Builder
    {
        return $query->where('jerarquia_id', $jerarquiaId);
    }

    public function scopeDeOficina(Builder $query, int $oficinaId): Builder
    {
        return $query->where('oficina_id', $oficinaId);
    }
}
