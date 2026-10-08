<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArmaModelo extends Model
{
    use RegistraAuditoria;

    protected $table = 'armas_modelos';

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
