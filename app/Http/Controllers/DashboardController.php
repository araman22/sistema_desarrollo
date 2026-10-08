<?php

namespace App\Http\Controllers;

use App\Models\CajaChicaMovimiento;
use App\Models\Documento;
use App\Models\EquipamientoTecnologico;
use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\TipoDocumento;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ultimoIngreso = CajaChicaMovimiento::query()->where('tipo', 'ingreso')->latest('fecha')->first();
        $ultimoEgreso = CajaChicaMovimiento::query()->where('tipo', 'egreso')->latest('fecha')->first();
        $totalIngresos = CajaChicaMovimiento::query()->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = CajaChicaMovimiento::query()->where('tipo', 'egreso')->sum('monto');
        $totalEnCaja = (float) $totalIngresos - (float) $totalEgresos;

        $stats = [
            'oficinas' => Oficina::count(),
            'inventarios' => Inventario::count(),
            'equipos' => EquipamientoTecnologico::count(),
            'tipos_documentos' => TipoDocumento::count(),
            'documentos' => Documento::count(),
            'ultimo_ingreso' => $ultimoIngreso?->monto ?? 0,
            'ultimo_egreso' => $ultimoEgreso?->monto ?? 0,
            'diferencia_caja_chica' => $totalEnCaja,
            'origen_ultimo_ingreso' => $ultimoIngreso?->origen ?? 'Sin registros',
            'destino_ultimo_egreso' => $ultimoEgreso?->destino ?? 'Sin registros',
        ];

        return view('dashboard', compact('stats'));
    }
}
