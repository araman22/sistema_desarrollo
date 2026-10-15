<?php

namespace App\Http\Controllers;

use App\Http\Requests\CajaChicaMovimientoRequest;
use App\Models\CajaChicaMovimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CajaChicaController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', CajaChicaMovimiento::class);

        $movimientos = CajaChicaMovimiento::query()
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15);

        $ultimoIngreso = CajaChicaMovimiento::query()
            ->where('tipo', 'ingreso')
            ->latest('fecha')
            ->first();

        $ultimoEgreso = CajaChicaMovimiento::query()
            ->where('tipo', 'egreso')
            ->latest('fecha')
            ->first();

        $totalIngresos = CajaChicaMovimiento::query()->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = CajaChicaMovimiento::query()->where('tipo', 'egreso')->sum('monto');
        $diferencia = (float) $totalIngresos - (float) $totalEgresos;

        return view('caja-chica.index', compact(
            'movimientos',
            'ultimoIngreso',
            'ultimoEgreso',
            'diferencia'
        ));
    }

    public function store(CajaChicaMovimientoRequest $request): RedirectResponse
    {
        Gate::authorize('create', CajaChicaMovimiento::class);

        $data = $request->validated();

        if ($request->hasFile('ticket')) {
            $data['ticket_path'] = $request->file('ticket')->store('caja-chica/tickets', 'local');
        }

        $data['usuario_id'] = auth()->id();

        CajaChicaMovimiento::create($data);

        return redirect()->route('caja-chica.index')->with('success', 'Movimiento registrado correctamente.');
    }

    public function downloadTicket(CajaChicaMovimiento $cajaChica): StreamedResponse
    {
        Gate::authorize('download', $cajaChica);

        abort_if(blank($cajaChica->ticket_path), 404, 'No existe comprobante asociado.');

        abort_unless(Storage::disk('local')->exists($cajaChica->ticket_path), 404);

        return Storage::disk('local')->download($cajaChica->ticket_path);
    }
}
