<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EquipamientoTecnologico extends Model
{
    use RegistraAuditoria;

    protected $table = 'equipamientos_tecnologicos';

    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'codigo',
        'tipo',
        'nombre',
        'marca',
        'modelo',
        'numero_serie',
        'procesador',
        'memoria_ram',
        'almacenamiento',
        'sistema_operativo',
        'direccion_ip',
        'direccion_mac',
        'oficina_id',
        'estado',
        'fecha_adquisicion',
        'codigo_qr',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function oficina(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function alertas(): MorphMany
    {
        return $this->morphMany(Alerta::class, 'alertable', 'alertable_tipo', 'alertable_id');
    }

    public function mantenimientos(): MorphMany
    {
        return $this->morphMany(Mantenimiento::class, 'mantenible', 'mantenible_tipo', 'mantenible_id');
    }

    public function scopeDeTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }
}
