<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiculoMantenimiento extends Model
{
    protected $table = 'vehiculo_mantenimientos';

    protected $fillable = ['tipo', 'descripcion', 'fecha', 'kilometraje', 'costo', 'proveedor', 'responsable_id', 'proxima_fecha', 'proximo_kilometraje', 'observaciones'];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'proxima_fecha' => 'date', 'kilometraje' => 'integer', 'proximo_kilometraje' => 'integer', 'costo' => 'decimal:2'];
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
