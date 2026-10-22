<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\EquipamientoTecnologico;
use App\Models\FondoFijoMovimiento;
use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'oficinas' => Oficina::count(),
            'inventarios' => Inventario::count(),
            'equipos' => EquipamientoTecnologico::count(),
            'tipos_documentos' => TipoDocumento::count(),
            'documentos' => Documento::count(),
        ];

        if (Gate::allows('viewAny', FondoFijoMovimiento::class)) {
            $ultimoIngreso = FondoFijoMovimiento::query()->where('tipo', 'ingreso')->latest('fecha')->first();
            $ultimoEgreso = FondoFijoMovimiento::query()->where('tipo', 'egreso')->latest('fecha')->first();
            $totalIngresos = FondoFijoMovimiento::query()->where('tipo', 'ingreso')->sum('monto');
            $totalEgresos = FondoFijoMovimiento::query()->where('tipo', 'egreso')->sum('monto');
            $totalEnCaja = (float) $totalIngresos - (float) $totalEgresos;

            $stats += [
                'ultimo_ingreso' => $ultimoIngreso?->monto ?? 0,
                'ultimo_egreso' => $ultimoEgreso?->monto ?? 0,
                'diferencia_fondo_fijo' => $totalEnCaja,
                'origen_ultimo_ingreso' => $ultimoIngreso?->origen ?? 'Sin registros',
                'destino_ultimo_egreso' => $ultimoEgreso?->destino ?? 'Sin registros',
            ];
        }

        return view('dashboard', compact('stats'));
    }
}
