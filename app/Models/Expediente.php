<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expediente extends Model
{
    use RegistraAuditoria;

    protected $table = 'expedientes';

    public const ESTADOS = [
        'en_tramite' => 'En trámite',
        'archivado' => 'Archivado',
        'finalizado' => 'Finalizado',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'bibliorato_id',
        'numero',
        'asunto',
        'origen',
        'destino',
        'fecha_emision',
        'fecha_recepcion',
        'estado',
        'responsable_id',
        'ubicacion_actual',
        'observaciones',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_recepcion' => 'date',
        ];
    }

    // Mientras un pase no se recibe, el expediente figura pendiente de recepción.
    public function getEstadoTextoAttribute(): string
    {
        return $this->paseEnCurso ? 'Pendiente de recepción' : (self::ESTADOS[$this->estado] ?? $this->estado);
    }

    public function bibliorato(): BelongsTo
    {
        return $this->belongsTo(Bibliorato::class, 'bibliorato_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function documentos(): BelongsToMany
    {
        return $this->belongsToMany(Documento::class, 'expediente_documento', 'expediente_id', 'documento_id')->withTimestamps();
    }

    public function pases(): HasMany
    {
        return $this->hasMany(ExpedientePase::class, 'expediente_id');
    }

    public function paseEnCurso(): HasOne
    {
        return $this->hasOne(ExpedientePase::class, 'expediente_id')->whereNull('fecha_recepcion')->latestOfMany('fecha_envio');
    }

    public function scopePendientesDeRecepcion(Builder $query): Builder
    {
        return $query->whereHas('pases', fn ($q) => $q->whereNull('fecha_recepcion'));
    }

    /**
     * Sincroniza los documentos asociados dejando constancia en auditoría.
     *
     * @param  list<int>  $ids
     */
    public function sincronizarDocumentos(array $ids): void
    {
        $anteriores = $this->documentos()->pluck('documentos.id')->sort()->values()->all();
        $this->documentos()->sync($ids);
        $nuevos = collect($ids)->map(fn ($id) => (int) $id)->sort()->values()->all();
        if ($anteriores !== $nuevos) {
            $this->registrarAuditoria('editar', ['documentos' => $anteriores], ['documentos' => $nuevos]);
        }
    }
}
