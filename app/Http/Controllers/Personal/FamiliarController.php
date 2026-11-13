<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personal\FamiliarRequest;
use App\Models\Familiar;
use App\Models\Policia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class FamiliarController extends Controller
{
    public function store(FamiliarRequest $request, Policia $policia): RedirectResponse
    {
        Gate::authorize('update', $policia);
        $policia->familiares()->create($request->validated());

        return redirect()->route('personal.show', $policia)->with('success', 'Familiar registrado.');
    }

    public function destroy(Policia $policia, Familiar $familiar): RedirectResponse
    {
        Gate::authorize('update', $policia);
        $familiar->delete();

        return redirect()->route('personal.show', $policia)->with('success', 'Familiar eliminado.');
    }
}
