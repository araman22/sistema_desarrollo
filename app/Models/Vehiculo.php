<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    use HasFactory;

    protected $fillable = ['tipo_vehiculo_id', 'dominio', 'anio', 'numero_chasis', 'numero_motor', 'kilometraje', 'responsable_id', 'estado', 'compania_seguro', 'numero_poliza', 'vencimiento_seguro', 'ultima_revision', 'proxima_revision', 'fecha_adquisicion', 'observaciones'];

    protected function casts(): array
    {
        return ['anio' => 'integer', 'kilometraje' => 'integer', 'vencimiento_seguro' => 'date', 'ultima_revision' => 'date', 'proxima_revision' => 'date', 'fecha_adquisicion' => 'date'];
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(ResponsablePolicial::class, 'responsable_id');
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(VehiculoMantenimiento::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(VehiculoDocumento::class);
    }
}
