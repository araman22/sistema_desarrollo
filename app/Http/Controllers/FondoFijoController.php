<?php

namespace App\Http\Controllers;

use App\Http\Requests\FondoFijoMovimientoRequest;
use App\Models\FondoFijoMovimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FondoFijoController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', FondoFijoMovimiento::class);

        $movimientos = FondoFijoMovimiento::query()
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15);

        $ultimoIngreso = FondoFijoMovimiento::query()
            ->where('tipo', 'ingreso')
            ->latest('fecha')
            ->first();

        $ultimoEgreso = FondoFijoMovimiento::query()
            ->where('tipo', 'egreso')
            ->latest('fecha')
            ->first();

        $totalIngresos = FondoFijoMovimiento::query()->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = FondoFijoMovimiento::query()->where('tipo', 'egreso')->sum('monto');
        $diferencia = (float) $totalIngresos - (float) $totalEgresos;

        return view('fondo-fijo.index', compact(
            'movimientos',
            'ultimoIngreso',
            'ultimoEgreso',
            'diferencia'
        ));
    }

    public function store(FondoFijoMovimientoRequest $request): RedirectResponse
    {
        Gate::authorize('create', FondoFijoMovimiento::class);

        $data = $request->validated();

        if ($request->hasFile('ticket')) {
            $data['ticket_path'] = $request->file('ticket')->store('fondo-fijo/tickets', 'local');
        }

        $data['usuario_id'] = auth()->id();

        FondoFijoMovimiento::create($data);

        return redirect()->route('fondo-fijo.index')->with('success', 'Movimiento registrado correctamente.');
    }

    public function downloadTicket(FondoFijoMovimiento $fondoFijo): StreamedResponse
    {
        Gate::authorize('download', $fondoFijo);

        abort_if(blank($fondoFijo->ticket_path), 404, 'No existe comprobante asociado.');

        abort_unless(Storage::disk('local')->exists($fondoFijo->ticket_path), 404);

        return Storage::disk('local')->download($fondoFijo->ticket_path);
    }
}
