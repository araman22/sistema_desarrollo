<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personal\NovedadRevistaRequest;
use App\Models\Policia;
use App\Services\Personal\GestorLicencias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class NovedadRevistaController extends Controller
{
    public function store(NovedadRevistaRequest $request, Policia $policia): RedirectResponse
    {
        Gate::authorize('update', $policia);
        GestorLicencias::registrarNovedad($policia, $request->validated());

        return redirect()->route('personal.show', $policia)->with('success', 'Novedad registrada.');
    }
}
