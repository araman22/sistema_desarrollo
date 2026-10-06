<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoLicencia extends Model
{
    use RegistraAuditoria;

    protected $table = 'tipo_licencias';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
    ];

    public function licencias(): HasMany
    {
        return $this->hasMany(Licencia::class, 'tipo_licencia_id');
    }
}
