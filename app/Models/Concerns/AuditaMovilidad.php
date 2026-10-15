<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

/** Auditoría obligatoria solo para los recursos del Grupo 3 B. */
trait AuditaMovilidad
{
    use RegistraAuditoria;

    protected function registrarAuditoria(string $accion, ?array $valorAnterior, ?array $valorNuevo): void
    {
        Auditoria::create([
            'usuario_id' => Auth::id(), 'accion' => $accion,
            'tabla_afectada' => $this->getTable(), 'registro_id' => $this->getKey(),
            'valor_anterior' => $this->normalizarAuditoria($valorAnterior),
            'valor_nuevo' => $this->normalizarAuditoria($valorNuevo),
            'direccion_ip' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
