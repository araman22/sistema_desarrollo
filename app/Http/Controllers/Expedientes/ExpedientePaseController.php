<?php

namespace App\Http\Controllers\Expedientes;

use App\Http\Controllers\Controller;
use App\Models\Expediente;
use App\Models\ExpedientePase;
use App\Services\Expedientes\GestorPases;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExpedientePaseController extends Controller
{
    public function store(Request $request, Expediente $expediente): RedirectResponse
    {
        Gate::authorize('pasar', $expediente);

        $datos = $request->validate([
            'destino' => ['required', 'string', 'max:150'],
            'fecha_envio' => ['required', 'date', 'before_or_equal:now'],
            'enviado_por_id' => ['nullable', 'exists:policias,id'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);
        GestorPases::enviar($expediente, $datos);

        return redirect()->route('expedientes.show', $expediente)->with('success', 'Pase enviado; queda pendiente de recepción.');
    }

    public function recibir(Request $request, Expediente $expediente, ExpedientePase $pase): RedirectResponse
    {
        Gate::authorize('pasar', $expediente);

        $datos = $request->validate([
            'fecha_recepcion' => ['required', 'date', 'before_or_equal:now'],
            'recibido_por_id' => ['nullable', 'exists:policias,id'],
            'observaciones_recepcion' => ['nullable', 'string', 'max:2000'],
        ]);
        GestorPases::recibir($pase, $datos);

        return redirect()->route('expedientes.show', $expediente)->with('success', 'Recepción registrada.');
    }
}
