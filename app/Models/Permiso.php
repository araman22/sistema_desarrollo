<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permiso extends Model
{
    use RegistraAuditoria;

    protected $table = 'permisos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'modulo',
        'accion',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'permiso_id', 'rol_id');
    }

    public function rolPermiso(): HasMany
    {
        return $this->hasMany(RolPermiso::class, 'permiso_id');
    }

    public function scopeDelModulo(Builder $query, string $modulo): Builder
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeConAccion(Builder $query, string $accion): Builder
    {
        return $query->where('accion', $accion);
    }
}
