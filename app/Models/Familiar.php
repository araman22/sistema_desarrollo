<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Familiar extends Model
{
    use RegistraAuditoria;

    protected $table = 'familiares';

    public const PARENTESCOS = ['conyuge' => 'Cónyuge', 'hijo' => 'Hijo/a'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'policia_id',
        'parentesco',
        'nombre',
        'apellido',
        'dni',
        'fecha_nacimiento',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido.', '.$this->nombre, ', ');
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }
}
