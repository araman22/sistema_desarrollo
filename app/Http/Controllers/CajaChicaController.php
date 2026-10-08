<?php

namespace App\Http\Controllers;

use App\Http\Requests\CajaChicaMovimientoRequest;
use App\Models\CajaChicaMovimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CajaChicaController extends Controller
{
    public function index(): View
    {
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

        $diferencia = ($ultimoIngreso?->monto ?? 0) - ($ultimoEgreso?->monto ?? 0);

        return view('caja-chica.index', compact(
            'movimientos',
            'ultimoIngreso',
            'ultimoEgreso',
            'diferencia'
        ));
    }

    public function store(CajaChicaMovimientoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('ticket')) {
            $data['ticket_path'] = $request->file('ticket')->store('caja-chica/tickets', 'local');
        }

        $data['usuario_id'] = auth()->id();

        CajaChicaMovimiento::create($data);

        return redirect()->route('caja-chica.index')->with('success', 'Movimiento registrado correctamente.');
    }

    public function downloadTicket(CajaChicaMovimiento $cajaChica): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        abort_if(blank($cajaChica->ticket_path), 404, 'No existe comprobante asociado.');

        return response()->download(storage_path('app/' . $cajaChica->ticket_path), 'ticket-compra-' . $cajaChica->id . '.pdf');
    }
}
