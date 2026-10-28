<?php

namespace App\Http\Controllers\Expedientes;

use App\Http\Controllers\Controller;
use App\Models\Bibliorato;
use App\Models\Expediente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BiblioratoController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Expediente::class);

        return view('expedientes.biblioratos', [
            'biblioratos' => Bibliorato::withCount('expedientes')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('categorias', Expediente::class);
        Bibliorato::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('biblioratos.index')->with('success', 'Bibliorato creado.');
    }

    // Los biblioratos no se eliminan: se desactivan para conservar los expedientes archivados en ellos.
    public function update(Request $request, Bibliorato $bibliorato): RedirectResponse
    {
        Gate::authorize('categorias', Expediente::class);
        $bibliorato->update($this->validar($request, $bibliorato) + ['activo' => $request->boolean('activo')]);

        return redirect()->route('biblioratos.index')->with('success', 'Bibliorato actualizado.');
    }

    /**
     * @return array{nombre: string, descripcion: ?string}
     */
    private function validar(Request $request, ?Bibliorato $bibliorato = null): array
    {
        $request->merge(['nombre' => preg_replace('/\s+/u', ' ', trim((string) $request->input('nombre')))]);

        return $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('biblioratos', 'nombre')->ignore($bibliorato?->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
