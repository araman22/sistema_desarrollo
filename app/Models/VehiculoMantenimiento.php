<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Se audita con MovilidadAuditoria dentro de la transacción de cada operación.
class VehiculoMantenimiento extends Model
{
    protected $table = 'vehiculo_mantenimientos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vehiculo_id',
        'tipo',
        'descripcion',
        'fecha',
        'kilometraje',
        'costo',
        'proveedor',
        'responsable_id',
        'proxima_fecha',
        'proximo_kilometraje',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'proxima_fecha' => 'date',
            'kilometraje' => 'integer',
            'proximo_kilometraje' => 'integer',
            'costo' => 'decimal:2',
        ];
    }

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }
}
