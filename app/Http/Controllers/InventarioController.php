<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventarioRequest;
use App\Models\Inventario;
use App\Models\Oficina;
use App\Models\Policia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Inventario::class);

        // Un valor vacío equivale a "Todas"; los filtros se combinan entre sí.
        $filtros = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', 'max:50'],
            'oficina_id' => ['nullable', 'integer'],
            'responsable_id' => ['nullable', 'integer'],
        ]);

        $query = Inventario::query()->filtrar($filtros);

        // Los totales se calculan sobre todos los registros filtrados, no sobre la página visible.
        $subtotales = (clone $query)
            ->selectRaw('oficina_id, count(*) as registros, sum(cantidad) as unidades')
            ->groupBy('oficina_id')
            ->get();
        $nombresOficinas = Oficina::query()->whereKey($subtotales->pluck('oficina_id')->filter())->pluck('nombre', 'id');
        $subtotales = $subtotales
            ->map(fn ($fila) => [
                'oficina_id' => $fila->oficina_id,
                'oficina' => $nombresOficinas[$fila->oficina_id] ?? 'Sin oficina',
                'registros' => (int) $fila->registros,
                'unidades' => (int) $fila->unidades,
            ])
            ->sortBy(fn ($fila) => [$fila['oficina_id'] === null ? 1 : 0, $fila['oficina']])
            ->values();
        $totalGeneral = ['registros' => $subtotales->sum('registros'), 'unidades' => $subtotales->sum('unidades')];

        // Con "Todas las oficinas" los registros se listan agrupados por oficina.
        $agrupado = empty($filtros['oficina_id']);
        if ($agrupado) {
            $query->orderByRaw('inventarios.oficina_id is null')
                ->orderBy(Oficina::select('nombre')->whereColumn('oficinas.id', 'inventarios.oficina_id'));
        }

        $inventarios = $query->with(['oficina', 'responsable.persona'])
            ->orderBy('codigo')
            ->paginate(10)
            ->appends(request()->query());

        $oficinas = Oficina::query()->orderBy('nombre')->get();
        $responsables = Policia::query()->with('persona')->orderBy('numero_legajo')->get();
        $categorias = collect(Inventario::CATEGORIAS)
            ->merge(Inventario::query()->distinct()->orderBy('categoria')->pluck('categoria'))
            ->unique()->values();

        return view('inventarios.index', [
            'inventarios' => $inventarios,
            'oficinas' => $oficinas,
            'responsables' => $responsables,
            'categorias' => $categorias,
            'estados' => Inventario::ESTADOS,
            'subtotales' => $subtotales,
            'subtotalesPorOficina' => $subtotales->keyBy(fn ($fila) => $fila['oficina_id'] ?? 'sin'),
            'totalGeneral' => $totalGeneral,
            'agrupado' => $agrupado,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Inventario::class);

        return view('inventarios.form', [
            'inventario' => new Inventario(),
            'oficinas' => Oficina::query()->orderBy('nombre')->get(),
            'responsables' => Policia::query()->with('persona')->orderBy('numero_legajo')->get(),
            'estados' => Inventario::ESTADOS,
            'categorias' => Inventario::CATEGORIAS,
        ]);
    }

    public function store(InventarioRequest $request): RedirectResponse
    {
        Gate::authorize('create', Inventario::class);

        $inventario = Inventario::create($request->validated());

        return redirect()->route('inventarios.show', $inventario)
            ->with('success', 'El recurso de inventario fue creado correctamente.');
    }

    public function show(Inventario $inventario): View
    {
        Gate::authorize('view', $inventario);

        $inventario->load(['oficina', 'responsable.persona']);

        return view('inventarios.show', compact('inventario'));
    }

    public function edit(Inventario $inventario): View
    {
        Gate::authorize('update', $inventario);

        return view('inventarios.form', [
            'inventario' => $inventario,
            'oficinas' => Oficina::query()->orderBy('nombre')->get(),
            'responsables' => Policia::query()->with('persona')->orderBy('numero_legajo')->get(),
            'estados' => Inventario::ESTADOS,
            'categorias' => Inventario::CATEGORIAS,
        ]);
    }

    public function update(InventarioRequest $request, Inventario $inventario): RedirectResponse
    {
        Gate::authorize('update', $inventario);

        $inventario->update($request->validated());

        return redirect()->route('inventarios.show', $inventario)
            ->with('success', 'El recurso de inventario fue actualizado correctamente.');
    }

    public function destroy(Inventario $inventario): RedirectResponse
    {
        Gate::authorize('delete', $inventario);

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
