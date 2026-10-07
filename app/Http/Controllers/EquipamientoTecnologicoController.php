<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipamientoTecnologicoRequest;
use App\Models\EquipamientoTecnologico;
use App\Models\Oficina;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipamientoTecnologicoController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', EquipamientoTecnologico::class);

        $query = EquipamientoTecnologico::query()->with('oficina');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%")
                    ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        if ($tipo = request('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($estado = request('estado')) {
            $query->where('estado', $estado);
        }

        if ($oficinaId = request('oficina_id')) {
            $query->where('oficina_id', $oficinaId);
        }

        $equipamientos = $query->orderBy('nombre')->paginate(10)->appends(request()->query());
        $oficinas = Oficina::query()->orderBy('nombre')->get();

        return view('equipamientos.index', compact('equipamientos', 'oficinas'));
    }

    public function create(): View
    {
        $this->authorize('create', EquipamientoTecnologico::class);

        return view('equipamientos.form', [
            'equipamiento' => new EquipamientoTecnologico(),
            'oficinas' => Oficina::query()->orderBy('nombre')->get(),
            'tipos' => ['Computadora', 'Servidor', 'Impresora', 'Switch', 'Router', 'Otro'],
            'estados' => ['activo', 'inactivo', 'en_mantenimiento', 'baja'],
        ]);
    }

    public function store(EquipamientoTecnologicoRequest $request): RedirectResponse
    {
        $this->authorize('create', EquipamientoTecnologico::class);

        $equipamiento = EquipamientoTecnologico::create($request->validated());

        return redirect()->route('equipamientos-tecnologicos.show', $equipamiento)
            ->with('success', 'El equipamiento tecnológico fue creado correctamente.');
    }

    public function show(EquipamientoTecnologico $equipamientoTecnologico): View
    {
        $this->authorize('view', $equipamientoTecnologico);

        $equipamientoTecnologico->load('oficina');

        return view('equipamientos.show', compact('equipamientoTecnologico'));
    }

    public function edit(EquipamientoTecnologico $equipamientoTecnologico): View
    {
        $this->authorize('update', $equipamientoTecnologico);

        return view('equipamientos.form', [
            'equipamiento' => $equipamientoTecnologico,
            'oficinas' => Oficina::query()->orderBy('nombre')->get(),
            'tipos' => ['Computadora', 'Servidor', 'Impresora', 'Switch', 'Router', 'Otro'],
            'estados' => ['activo', 'inactivo', 'en_mantenimiento', 'baja'],
        ]);
    }

    public function update(EquipamientoTecnologicoRequest $request, EquipamientoTecnologico $equipamientoTecnologico): RedirectResponse
    {
        $this->authorize('update', $equipamientoTecnologico);

        $equipamientoTecnologico->update($request->validated());

        return redirect()->route('equipamientos-tecnologicos.show', $equipamientoTecnologico)
            ->with('success', 'El equipamiento tecnológico fue actualizado correctamente.');
    }

    public function destroy(EquipamientoTecnologico $equipamientoTecnologico): RedirectResponse
    {
        $this->authorize('delete', $equipamientoTecnologico);

        if ($equipamientoTecnologico->mantenimientos()->exists() || $equipamientoTecnologico->alertas()->exists()) {
            return back()->withErrors([
                'equipamiento' => 'No se puede eliminar porque tiene mantenimiento o alertas asociadas.',
            ]);
        }

        $equipamientoTecnologico->delete();

        return redirect()->route('equipamientos-tecnologicos.index')
            ->with('success', 'El equipamiento tecnológico fue eliminado correctamente.');
    }
}
