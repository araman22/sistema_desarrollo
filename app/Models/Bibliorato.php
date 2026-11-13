<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bibliorato extends Model
{
    use RegistraAuditoria;

    protected $table = 'biblioratos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'bibliorato_id');
    }
}
