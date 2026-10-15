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
        'ruta_archivo',
        'mime_type',
        'tamano_bytes',
        'extension',
        'archivo_original',
        'hash_archivo',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'tamano_bytes' => 'integer',
        ];
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
