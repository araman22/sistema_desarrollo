<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vehiculo extends Model
{
    use RegistraAuditoria;

    protected $table = 'vehiculos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo_vehiculo_id',
        'dominio',
        'anio',
        'numero_chasis',
        'numero_motor',
        'kilometraje',
        'responsable_id',
        'estado',
        'compania_seguro',
        'numero_poliza',
        'vencimiento_seguro',
        'ultima_revision',
        'proxima_revision',
        'fecha_adquisicion',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'kilometraje' => 'integer',
            'vencimiento_seguro' => 'date',
            'ultima_revision' => 'date',
            'proxima_revision' => 'date',
            'fecha_adquisicion' => 'date',
        ];
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(VehiculoMantenimiento::class, 'vehiculo_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function scopeRevisionProxima(Builder $query): Builder
    {
        return $query->whereNotNull('proxima_revision')->whereDate('proxima_revision', '<=', now());
    }

    public function scopeSeguroVencido(Builder $query): Builder
    {
        return $query->whereNotNull('vencimiento_seguro')->whereDate('vencimiento_seguro', '<=', now());
    }
}
