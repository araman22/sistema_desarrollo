<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpedientePase extends Model
{
    use RegistraAuditoria;

    protected $table = 'expediente_pases';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'expediente_id',
        'origen',
        'destino',
        'fecha_envio',
        'fecha_recepcion',
        'enviado_por_id',
        'recibido_por_id',
        'observaciones',
        'observaciones_recepcion',
        'usuario_envio_id',
        'usuario_recepcion_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_envio' => 'datetime',
            'fecha_recepcion' => 'datetime',
        ];
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'enviado_por_id');
    }

    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'recibido_por_id');
    }

    public function usuarioEnvio(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_envio_id');
    }

    public function usuarioRecepcion(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_recepcion_id');
    }
}
