<?php

namespace App\Services\Personal;

use App\Models\Licencia;
use App\Models\NovedadRevista;
use App\Models\Policia;
use App\Models\TipoLicencia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestorLicencias
{
    public const CUPO_BASE = 30;

    public const CUPO_ANTIGUEDAD = 45;

    public const ANIOS_PARA_CUPO_MAYOR = 15;

    /**
     * 30 días corridos para los años anteriores a aquel en que se cumplen 15 años
     * de antigüedad; 45 desde ese año en adelante.
     */
    public static function cupoAnual(Policia $policia, int $anio): int
    {
        if (! $policia->fecha_ingreso) {
            return self::CUPO_BASE;
        }

        return $anio < $policia->fecha_ingreso->year + self::ANIOS_PARA_CUPO_MAYOR ? self::CUPO_BASE : self::CUPO_ANTIGUEDAD;
    }

    public static function diasUtilizados(Policia $policia, int $anio, ?int $exceptoLicenciaId = null): int
    {
        return (int) Licencia::query()
            ->where('policia_id', $policia->id)
            ->where('anio_correspondiente', $anio)
            ->vigentes()
            ->whereHas('tipo', fn ($q) => $q->where('computa_cupo_anual', true))
            ->when($exceptoLicenciaId, fn ($q) => $q->whereKeyNot($exceptoLicenciaId))
            ->sum('cantidad_dias');
    }

    /**
     * @return array{anio: int, cupo: int, utilizados: int, disponibles: int}
     */
    public static function resumen(Policia $policia, int $anio, ?int $exceptoLicenciaId = null): array
    {
        $cupo = self::cupoAnual($policia, $anio);
        $utilizados = self::diasUtilizados($policia, $anio, $exceptoLicenciaId);

        return ['anio' => $anio, 'cupo' => $cupo, 'utilizados' => $utilizados, 'disponibles' => max(0, $cupo - $utilizados)];
    }

    /**
     * Crea o actualiza la licencia validando cupo y superposición dentro de una
     * transacción que bloquea al agente, para que dos pedidos simultáneos no excedan el cupo.
     *
     * @param  array<string, mixed>  $datos
     */
    public static function guardar(Licencia $licencia, array $datos): Licencia
    {
        return DB::transaction(function () use ($licencia, $datos) {
            $policia = Policia::query()->lockForUpdate()->findOrFail($datos['policia_id']);
            $licencia->fill($datos);
            if (! ($datos['requiere_reemplazo'] ?? false)) {
                $licencia->fill(['reemplazo_id' => null, 'cobertura_desde' => null, 'cobertura_hasta' => null]);
            }
            $desde = $licencia->fecha_inicio->toDateString();
            $hasta = $licencia->fecha_inicio->copy()->addDays($licencia->cantidad_dias - 1)->toDateString();

            if (in_array($licencia->estado, Licencia::ESTADOS_VIGENTES, true)) {
                self::validarSuperposicion($licencia, $policia, $desde, $hasta);
                self::validarCupo($licencia, $policia);
            }

            $licencia->save();
            self::sincronizarNovedad($licencia);

            return $licencia;
        });
    }

    private static function validarSuperposicion(Licencia $licencia, Policia $policia, string $desde, string $hasta): void
    {
        $superpuesta = Licencia::query()->where('policia_id', $policia->id)->vigentes()
            ->superpuestaCon($desde, $hasta)
            ->when($licencia->exists, fn ($q) => $q->whereKeyNot($licencia->id))
            ->first();
        if ($superpuesta) {
            throw ValidationException::withMessages(['fecha_inicio' => 'El agente ya tiene una licencia entre el '.$superpuesta->fecha_inicio->format('d/m/Y').' y el '.$superpuesta->fecha_fin->format('d/m/Y').'.']);
        }

        if ($licencia->reemplazo_id) {
            $ocupado = Licencia::query()->where('policia_id', $licencia->reemplazo_id)->vigentes()->superpuestaCon($desde, $hasta)->exists();
            if ($ocupado) {
                throw ValidationException::withMessages(['reemplazo_id' => 'El reemplazo elegido tiene una licencia en el mismo período.']);
            }
        }
    }

    private static function validarCupo(Licencia $licencia, Policia $policia): void
    {
        if (! TipoLicencia::whereKey($licencia->tipo_licencia_id)->value('computa_cupo_anual')) {
            return;
        }
        $resumen = self::resumen($policia, $licencia->anio_correspondiente, $licencia->exists ? $licencia->id : null);
        if ($licencia->cantidad_dias > $resumen['disponibles']) {
            throw ValidationException::withMessages(['cantidad_dias' => "Supera el cupo anual {$resumen['anio']}: quedan {$resumen['disponibles']} de {$resumen['cupo']} días corridos."]);
        }
    }

    // Una licencia autorizada figura en la situación de revista durante su período real.
    private static function sincronizarNovedad(Licencia $licencia): void
    {
        $novedad = $licencia->novedad()->first();
        if ($licencia->estado !== 'autorizada') {
            $novedad?->delete();

            return;
        }
        $regreso = $licencia->fecha_regreso_efectivo ?? $licencia->fecha_regreso_prevista;
        $datos = [
            'policia_id' => $licencia->policia_id,
            'situacion' => 'licencia',
            'fecha_desde' => $licencia->fecha_inicio,
            'fecha_hasta' => $regreso->copy()->subDay(),
            'observaciones' => $licencia->tipo?->nombre,
        ];
        $novedad ? $novedad->update($datos) : $licencia->novedad()->create($datos + ['usuario_id' => Auth::id()]);
    }

    /**
     * Registra una novedad manual. Si la situación anterior seguía abierta, se cierra
     * el día previo para que el historial no tenga huecos ni se pierda.
     *
     * @param  array<string, mixed>  $datos
     */
    public static function registrarNovedad(Policia $policia, array $datos): NovedadRevista
    {
        return DB::transaction(function () use ($policia, $datos) {
            Policia::query()->lockForUpdate()->findOrFail($policia->id);
            $novedad = new NovedadRevista($datos + ['policia_id' => $policia->id, 'usuario_id' => Auth::id()]);

            $abiertas = $policia->novedades()->whereNull('licencia_id')->whereNull('fecha_hasta')
                ->whereDate('fecha_desde', '<', $novedad->fecha_desde)->get();
            foreach ($abiertas as $abierta) {
                $abierta->update(['fecha_hasta' => $novedad->fecha_desde->copy()->subDay()]);
            }
            $novedad->save();

            return $novedad;
        });
    }
}
