<?php

namespace App\Models;

use App\Models\Concerns\RegistraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVehiculo extends Model
{
    use RegistraAuditoria;

    protected $table = 'tipo_vehiculos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
    ];

    private const SIN_TILDES = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
    ];

    protected static function booted(): void
    {
        static::saving(function (TipoVehiculo $tipo) {
            $tipo->nombre = self::limpiar($tipo->nombre);
            $tipo->nombre_normalizado = self::normalizar($tipo->nombre);
        });
    }

    // Conserva la escritura original; solo quita espacios sobrantes.
    public static function limpiar(?string $nombre): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $nombre));
    }

    // Clave de comparación sin tildes ni mayúsculas. La ñ no se reemplaza: "Caña" y "Cana" son distintos.
    public static function normalizar(?string $nombre): string
    {
        return strtr(mb_strtolower(self::limpiar($nombre), 'UTF-8'), self::SIN_TILDES);
    }

    public static function equivalenteA(?string $nombre, ?int $exceptoId = null): ?self
    {
        return static::query()
            ->where('nombre_normalizado', self::normalizar($nombre))
            ->when($exceptoId, fn ($q) => $q->whereKeyNot($exceptoId))
            ->first();
    }

    // Para seeders y factories: reutiliza el tipo equivalente aunque esté escrito distinto.
    public static function obtener(string $nombre): self
    {
        return static::firstOrCreate(['nombre_normalizado' => self::normalizar($nombre)], ['nombre' => self::limpiar($nombre)]);
    }

    public function vehiculos(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'tipo_vehiculo_id');
    }
}
