<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    use RegistraAuditoria;

    protected $table = 'personas';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dni',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'correo_electronico',
        'foto',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido);
    }

    /**
     * Un policeman como maximo por persona (persona_id es unique en policias).
     */
    public function policia(): HasOne
    {
        return $this->hasOne(Policia::class, 'persona_id');
    }

    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function (Builder $query) use ($termino) {
            $query->where('dni', 'like', "%{$termino}%")
                ->orWhere('nombre', 'like', "%{$termino}%")
                ->orWhere('apellido', 'like', "%{$termino}%");
        });
    }
}
