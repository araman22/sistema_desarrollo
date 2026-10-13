<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacitacionPersonal extends Model
{
    use RegistraAuditoria;

    protected $table = 'capacitacion_personal';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'capacitacion_id',
        'policia_id',
        'resultado',
        'observaciones',
    ];

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'capacitacion_id');
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }
}
