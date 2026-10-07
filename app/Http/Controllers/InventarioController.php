<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventarioRequest;
use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\Policia;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Inventario::class);

        $query = Inventario::query()->with(['oficina', 'responsable.persona']);

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        if ($categoria = request('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($estado = request('estado')) {
            $query->where('estado', $estado);
        }

        if ($oficinaId = request('oficina_id')) {
            $query->where('oficina_id', $oficinaId);
        }

        if ($responsableId = request('responsable_id')) {
            $query->where('responsable_id', $responsableId);
        }

        $inventarios = $query->orderBy('codigo')->paginate(10)->appends(request()->query());
        $oficinas = Oficina::query()->orderBy('nombre')->get();
        $responsables = Policia::query()->with('persona')->orderBy('numero_legajo')->get();

        return view('inventarios.index', compact('inventarios', 'oficinas', 'responsables'));
    }

    public function create(): View
    {
        $this->authorize('create', Inventario::class);

        return view('inventarios.form', [
            'inventario' => new Inventario(),
            'oficinas' => Oficina::query()->orderBy('nombre')->get(),
            'responsables' => Policia::query()->with('persona')->orderBy('numero_legajo')->get(),
            'estados' => ['disponible', 'en_uso', 'en_mantenimiento', 'baja', 'perdido'],
            'categorias' => ['Tecnologia', 'Mobiliario', 'Herramienta', 'Otro'],
        ]);
    }

    public function store(InventarioRequest $request): RedirectResponse
    {
        $this->authorize('create', Inventario::class);

        $inventario = Inventario::create($request->validated());

        return redirect()->route('inventarios.show', $inventario)
            ->with('success', 'El recurso de inventario fue creado correctamente.');
    }

    public function show(Inventario $inventario): View
    {
        $this->authorize('view', $inventario);

        $inventario->load(['oficina', 'responsable.persona']);

        return view('inventarios.show', compact('inventario'));
    }

    public function edit(Inventario $inventario): View
    {
        $this->authorize('update', $inventario);

        return view('inventarios.form', [
            'inventario' => $inventario,
            'oficinas' => Oficina::query()->orderBy('nombre')->get(),
            'responsables' => Policia::query()->with('persona')->orderBy('numero_legajo')->get(),
            'estados' => ['disponible', 'en_uso', 'en_mantenimiento', 'baja', 'perdido'],
            'categorias' => ['Tecnologia', 'Mobiliario', 'Herramienta', 'Otro'],
        ]);
    }

    public function update(InventarioRequest $request, Inventario $inventario): RedirectResponse
    {
        $this->authorize('update', $inventario);

        $inventario->update($request->validated());

        return redirect()->route('inventarios.show', $inventario)
            ->with('success', 'El recurso de inventario fue actualizado correctamente.');
    }

    public function destroy(Inventario $inventario): RedirectResponse
    {
        $this->authorize('delete', $inventario);

        if ($inventario->mantenimientos()->exists() || $inventario->alertas()->exists()) {
            return back()->withErrors([
                'inventario' => 'No se puede eliminar porque tiene mantenimientos o alertas asociadas.',
            ]);
        }

        $inventario->delete();

        return redirect()->route('inventarios.index')
            ->with('success', 'El recurso de inventario fue eliminado correctamente.');
    }
}
