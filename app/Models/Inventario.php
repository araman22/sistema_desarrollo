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

    public const CATEGORIAS = ['Tecnologia', 'Mobiliario', 'Herramienta', 'Otro'];

    public const ESTADOS = ['disponible', 'en_uso', 'en_mantenimiento', 'baja', 'perdido'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'codigo',
        'descripcion',
        'categoria',
        'cantidad',
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
            'cantidad' => 'integer',
            'fecha_adquisicion' => 'date',
            'valor_adquisicion' => 'decimal:2',
        ];
    }

    /**
     * @param  array{search?: ?string, categoria?: ?string, estado?: ?string, oficina_id?: ?int, responsable_id?: ?int}  $filtros
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            }))
            ->when($filtros['categoria'] ?? null, fn ($q, $categoria) => $q->where('categoria', $categoria))
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['oficina_id'] ?? null, fn ($q, $oficinaId) => $q->where('oficina_id', $oficinaId))
            ->when($filtros['responsable_id'] ?? null, fn ($q, $responsableId) => $q->where('responsable_id', $responsableId));
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
