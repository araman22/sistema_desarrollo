<?php

namespace App\Http\Controllers;

use App\Http\Requests\OficinaRequest;
use App\Models\Oficina;
use App\Models\Policia;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OficinaController extends Controller
{
    public function index(): View
    {
        $query = Oficina::query()->with(['responsable.persona']);

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('ubicacion', 'like', "%{$search}%")
                    ->orWhere('correo_electronico', 'like', "%{$search}%");
            });
        }

        if ($estado = request('estado')) {
            $query->where('estado', $estado);
        }

        if ($responsableId = request('responsable_id')) {
            $query->where('responsable_id', $responsableId);
        }

        $oficinas = $query->orderBy('nombre')
            ->paginate(10)
            ->appends(request()->query());

        $responsables = Policia::query()->with('persona')->orderBy('numero_legajo')->get();

        return view('oficinas.index', compact('oficinas', 'responsables'));
    }

    public function create(): View
    {
        return view('oficinas.form', [
            'oficina' => new Oficina(),
            'responsables' => Policia::query()->with('persona')->orderBy('numero_legajo')->get(),
            'estados' => ['activa', 'inactiva', 'cerrada'],
        ]);
    }

    public function store(OficinaRequest $request): RedirectResponse
    {
        $oficina = Oficina::create($request->validated());

        return redirect()->route('oficinas.show', $oficina)
            ->with('success', 'La oficina fue creada correctamente.');
    }

    public function show(Oficina $oficina): View
    {
        $oficina->load(['responsable.persona', 'policias.persona', 'inventarios', 'equipamiento']);

        return view('oficinas.show', compact('oficina'));
    }

    public function edit(Oficina $oficina): View
    {
        return view('oficinas.form', [
            'oficina' => $oficina,
            'responsables' => Policia::query()->with('persona')->orderBy('numero_legajo')->get(),
            'estados' => ['activa', 'inactiva', 'cerrada'],
        ]);
    }

    public function update(OficinaRequest $request, Oficina $oficina): RedirectResponse
    {
        $oficina->update($request->validated());

        return redirect()->route('oficinas.show', $oficina)
            ->with('success', 'La oficina fue actualizada correctamente.');
    }

    public function destroy(Oficina $oficina): RedirectResponse
    {
        if ($oficina->policias()->exists() || $oficina->inventarios()->exists() || $oficina->equipamiento()->exists() || $oficina->proyectos()->exists()) {
            return back()->withErrors([
                'oficina' => 'No se puede eliminar la oficina porque existen registros asociados.',
            ]);
        }

        $oficina->delete();

        return redirect()->route('oficinas.index')
            ->with('success', 'La oficina fue eliminada correctamente.');
    }
}
