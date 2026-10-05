<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de operaciones. NO usa el trait RegistraAuditoria para evitar
 * que cada escritura genere otra auditoria de forma recursiva.
 */
class Auditoria extends Model
{
    protected $table = 'auditorias';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla_afectada',
        'registro_id',
        'valor_anterior',
        'valor_nuevo',
        'direccion_ip',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor_anterior' => 'array',
            'valor_nuevo' => 'array',
        ];
    }

    /**
     * Usuario que ejecuto la operacion.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
