<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funcion extends Model
{
    use RegistraAuditoria;

    protected $table = 'funciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
    ];

    public function policias(): HasMany
    {
        return $this->hasMany(Policia::class, 'funcion_id');
    }
}
