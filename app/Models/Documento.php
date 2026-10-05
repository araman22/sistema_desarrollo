<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    use RegistraAuditoria;

    protected $table = 'documentos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tipo_documento_id',
        'nombre',
        'numero',
        'descripcion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero' => 'integer',
        ];
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }
}
