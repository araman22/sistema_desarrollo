<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoIntegrante extends Model
{
    use RegistraAuditoria;

    protected $table = 'proyecto_integrantes';

    /**
     * Tabla puente: no registra created_at ni updated_at.
     */
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'proyecto_id',
        'policia_id',
        'fecha_ingreso',
        'fecha_salida',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'fecha_salida' => 'date',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }
}
