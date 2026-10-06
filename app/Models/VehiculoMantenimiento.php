<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiculoMantenimiento extends Model
{
    use RegistraAuditoria;

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
