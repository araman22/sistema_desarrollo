<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jerarquia extends Model
{
    use RegistraAuditoria;

    protected $table = 'jerarquias';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
    ];

    public function policias(): HasMany
    {
        return $this->hasMany(Policia::class, 'jerarquia_id');
    }
}
