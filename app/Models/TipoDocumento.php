<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoDocumento extends Model
{
    use RegistraAuditoria;

    protected $table = 'tipo_documentos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
    ];

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'tipo_documento_id');
    }
}
