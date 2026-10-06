<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Registra en la tabla auditorias las operaciones de creacion, actualizacion,
 * eliminacion y restauracion sobre el modelo que use el trait.
 *
 * No debe aplicarse al propio modelo Auditoria para evitar recursion.
 */
trait RegistraAuditoria
{
    public static function bootRegistraAuditoria(): void
    {
        static::created(function (Model $modelo) {
            $modelo->registrarAuditoria('crear', null, $modelo->getAttributes());
        });

        static::updated(function (Model $modelo) {
            $modelo->registrarAuditoria(
                'editar',
                $modelo->getOriginal(),
                $modelo->getChanges(),
            );
        });

        static::deleted(function (Model $modelo) {
            $modelo->registrarAuditoria('eliminar', $modelo->getOriginal(), null);
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function (Model $modelo) {
                $modelo->registrarAuditoria('restaurar', null, $modelo->getAttributes());
            });
        }
    }

    /**
     * Filas de auditoria correspondientes a este registro concreto.
     *
     * La tabla auditorias no es una relacion polimorfica clasica, por eso se
     * resuelve de forma manual con tabla_afectada + registro_id.
     */
    public function auditoriasDelRegistro()
    {
        return Auditoria::query()
            ->where('tabla_afectada', $this->getTable())
            ->where('registro_id', $this->getKey())
            ->orderByDesc('created_at');
    }

    /**
     * Escribe una fila en auditorias. Nunca debe romper la operacion principal.
     */
    protected function registrarAuditoria(string $accion, ?array $valorAnterior, ?array $valorNuevo): void
    {
        try {
            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => $accion,
                'tabla_afectada' => $this->getTable(),
                'registro_id' => $this->getKey(),
                'valor_anterior' => $this->normalizarAuditoria($valorAnterior),
                'valor_nuevo' => $this->normalizarAuditoria($valorNuevo),
                'direccion_ip' => request()->ip(),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar la auditoria: '.$e->getMessage(), [
                'modelo' => static::class,
                'registro_id' => $this->getKey(),
            ]);
        }
    }

    /**
     * Filtra y normaliza los atributos para poder guardarlos como JSON,
     * descartando claves protegidas y developando enums.
     *
     * @param  array<string, mixed>|null  $atributos
     * @return array<string, mixed>|null
     */
    protected function normalizarAuditoria(?array $atributos): ?array
    {
        if ($atributos === null) {
            return null;
        }

        $atributos = array_diff_key($atributos, array_flip($this->auditarExcluir()));

        $normalizados = [];

        foreach ($atributos as $clave => $valor) {
            $normalizados[$clave] = $valor instanceof BackedEnum ? $valor->value : $valor;
        }

        return $normalizados;
    }

    /**
     * Atributos que nunca deben quedar almacenados en la auditoria.
     *
     * @return array<int, string>
     */
    protected function auditarExcluir(): array
    {
        return property_exists($this, 'auditarExcepto')
            ? $this->auditarExcepto
            : ['contrasena', 'recordar_token', 'password', 'remember_token'];
    }
}
