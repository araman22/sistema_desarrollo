<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArmaModelo extends Model
{
    use RegistraAuditoria;

    protected $table = 'armas_modelos';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * Modelo del catalogo (marca + modelo). El arma fisica y su numero de
     * serie viven en `armas`.
     *
     * @var list<string>
     */
    protected $fillable = [
        'marca',
        'modelo',
        'calibre',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function armas(): HasMany
    {
        return $this->hasMany(Arma::class, 'modelo_arma_id');
    }

    /**
     * Etiqueta lista para mostrar.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->marca.' '.$this->modelo);
    }
}
