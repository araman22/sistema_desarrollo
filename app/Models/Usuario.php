<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable, RegistraAuditoria;

    protected $table = 'usuarios';

    /**
     * La tabla usuarios no usa created_at / updated_at.
     */
    /**
     * Laravel espera "remember_token"; la columna del proyecto es recordar_token.
     */
    protected $rememberTokenName = 'recordar_token';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'policia_id',
        'rol_id',
        'nombre_usuario',
        'correo_electronico',
        'contrasena',
        'activo',
        'ultimo_acceso',
        'recordar_token',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'contrasena',
        'recordar_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    protected static function newFactory(): UsuarioFactory
    {
        return UsuarioFactory::new();
    }

    public function policia(): BelongsTo
    {
        return $this->belongsTo(Policia::class, 'policia_id');
    }

    /**
     * Un usuario tiene exactamente un rol (usuarios.rol_id, con restrictOnDelete).
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'usuario_id');
    }

    /**
     * Permisos efectivos: los del rol asignado al usuario.
     */
    public function permisos()
    {
        return Permiso::query()->whereIn('id', function ($query) {
            $query->select('permiso_id')
                ->from('rol_permiso')
                ->where('rol_id', $this->rol_id);
        });
    }

    public function tienePermiso(string $nombre): bool
    {
        return $this->permisos()->where('nombre', $nombre)->exists();
    }

    public function tieneRol(string $nombre): bool
    {
        return $this->rol?->nombre === $nombre;
    }

    public function estaActivo(): bool
    {
        return (bool) $this->activo;
    }

    /**
     * El broker de passwords usa la columna "email" de password_reset_tokens,
     * aqui el correo vive en correo_electronico.
     */
    public function getEmailForPasswordReset(): ?string
    {
        return $this->correo_electronico;
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }
}
