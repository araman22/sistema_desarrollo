<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoDocumentoRequest;
use App\Models\TipoDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TipoDocumentoController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TipoDocumento::class);

        $query = TipoDocumento::query();

        if ($search = request('search')) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        $tipos = $query->orderBy('nombre')->paginate(10)->appends(request()->query());

        return view('tipo-documentos.index', compact('tipos'));
    }

    public function create(): View
    {
        $this->authorize('create', TipoDocumento::class);

        return view('tipo-documentos.form', ['tipoDocumento' => new TipoDocumento()]);
    }

    public function store(TipoDocumentoRequest $request): RedirectResponse
    {
        $this->authorize('create', TipoDocumento::class);

        $tipoDocumento = TipoDocumento::create($request->validated());

        return redirect()->route('tipo-documentos.show', $tipoDocumento)
            ->with('success', 'El tipo de documento fue creado correctamente.');
    }

    public function show(TipoDocumento $tipoDocumento): View
    {
        $this->authorize('view', $tipoDocumento);

        $tipoDocumento->load('documentos');

        return view('tipo-documentos.show', compact('tipoDocumento'));
    }

    public function edit(TipoDocumento $tipoDocumento): View
    {
        $this->authorize('update', $tipoDocumento);

        return view('tipo-documentos.form', ['tipoDocumento' => $tipoDocumento]);
    }

    public function update(TipoDocumentoRequest $request, TipoDocumento $tipoDocumento): RedirectResponse
    {
        $this->authorize('update', $tipoDocumento);

        $tipoDocumento->update($request->validated());

        return redirect()->route('tipo-documentos.show', $tipoDocumento)
            ->with('success', 'El tipo de documento fue actualizado correctamente.');
    }

    public function destroy(TipoDocumento $tipoDocumento): RedirectResponse
    {
        $this->authorize('delete', $tipoDocumento);

        if ($tipoDocumento->documentos()->exists()) {
            return back()->withErrors([
                'tipoDocumento' => 'No se puede eliminar porque hay documentos asociados.',
            ]);
        }

        $tipoDocumento->delete();

        return redirect()->route('tipo-documentos.index')
            ->with('success', 'El tipo de documento fue eliminado correctamente.');
    }
}
