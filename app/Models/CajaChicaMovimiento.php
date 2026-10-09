<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CajaChicaMovimiento extends Model
{
    protected $table = 'caja_chica_movimientos';

    protected $fillable = [
        'tipo',
        'monto',
        'origen',
        'destino',
        'fecha',
        'descripcion',
        'ticket_path',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
