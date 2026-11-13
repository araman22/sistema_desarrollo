<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Personal\LicenciaRequest;
use App\Models\Licencia;
use App\Models\Persona;
use App\Models\Policia;
use App\Models\TipoLicencia;
use App\Services\Personal\GestorLicencias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LicenciaController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Licencia::class);

        $filtros = request()->validate([
            'policia_id' => ['nullable', 'integer'],
            'tipo_licencia_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'anio' => ['nullable', 'integer'],
        ]);

        $licencias = Licencia::query()
            ->with(['policia.persona', 'tipo', 'reemplazo.persona'])
            ->when($filtros['policia_id'] ?? null, fn ($q, $id) => $q->where('policia_id', $id))
            ->when($filtros['tipo_licencia_id'] ?? null, fn ($q, $id) => $q->where('tipo_licencia_id', $id))
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
            ->when($filtros['anio'] ?? null, fn ($q, $anio) => $q->where('anio_correspondiente', $anio))
            ->orderByDesc('fecha_inicio')
            ->paginate(15)
            ->appends(request()->query());

        return view('licencias.index', [
            'licencias' => $licencias,
            'policias' => $this->agentes(),
            'tipos' => TipoLicencia::orderBy('nombre')->get(),
            'estados' => Licencia::ESTADOS,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Licencia::class);

        return $this->formulario(new Licencia([
            'policia_id' => request('policia_id'),
            'anio_correspondiente' => today()->year,
            'fecha_inicio' => today(),
            'estado' => 'pendiente',
        ]));
    }

    public function store(LicenciaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Licencia::class);
        $licencia = GestorLicencias::guardar(new Licencia(), $request->validated());

        return redirect()->route('licencias.show', $licencia)->with('success', 'La licencia fue registrada.');
    }

    public function show(Licencia $licencia): View
    {
        Gate::authorize('view', $licencia);
        $licencia->load(['policia.persona', 'tipo', 'reemplazo.persona', 'novedad']);

        return view('licencias.show', [
            'licencia' => $licencia,
            'cupo' => $licencia->tipo?->computa_cupo_anual ? GestorLicencias::resumen($licencia->policia, $licencia->anio_correspondiente) : null,
        ]);
    }

    public function edit(Licencia $licencia): View
    {
        Gate::authorize('update', $licencia);

        return $this->formulario($licencia);
    }

    public function update(LicenciaRequest $request, Licencia $licencia): RedirectResponse
    {
        Gate::authorize('update', $licencia);
        GestorLicencias::guardar($licencia, $request->validated());

        return redirect()->route('licencias.show', $licencia)->with('success', 'La licencia fue actualizada.');
    }

    public function destroy(Licencia $licencia): RedirectResponse
    {
        Gate::authorize('delete', $licencia);
        $licencia->delete();

        return redirect()->route('licencias.index')->with('success', 'La licencia pendiente fue eliminada.');
    }

    private function formulario(Licencia $licencia): View
    {
        $policia = $licencia->policia_id ? Policia::find($licencia->policia_id) : null;

        return view('licencias.form', [
            'licencia' => $licencia,
            'policias' => $this->agentes(),
            'tipos' => TipoLicencia::orderBy('nombre')->get(),
            'estados' => Licencia::ESTADOS,
            'puedeAutorizar' => request()->user()->can('autorizar', Licencia::class),
            'cupo' => $policia ? GestorLicencias::resumen($policia, $licencia->anio_correspondiente ?? today()->year, $licencia->exists ? $licencia->id : null) : null,
        ]);
    }

    private function agentes()
    {
        return Policia::query()->with('persona')
            ->orderBy(Persona::select('apellido')->whereColumn('personas.id', 'policias.persona_id'))
            ->get();
    }
}
