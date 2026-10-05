<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Inventario extends Model
{
    use RegistraAuditoria;

    protected $table = 'inventarios';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'codigo',
        'descripcion',
        'categoria',
        'marca',
        'modelo',
        'numero_serie',
        'estado',
        'ubicacion',
        'oficina_id',
        'responsable_id',
        'fecha_adquisicion',
        'valor_adquisicion',
        'observaciones',
        'codigo_qr',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'valor_adquisicion' => 'decimal:2',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'responsable_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function mantenimientos(): MorphMany
    {
        return $this->morphMany(Mantenimiento::class, 'mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    public function scopeDeCategoria(Builder $query, string $categoria): Builder
    {
        return $query->where('categoria', $categoria);
    }
}
