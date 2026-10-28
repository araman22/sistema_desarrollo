<?php

namespace App\Http\Controllers;

use App\Http\Requests\Personal\PoliciaRequest;
use App\Models\Funcion;
use App\Models\Jerarquia;
use App\Models\Licencia;
use App\Models\NovedadRevista;
use App\Models\Oficina;
use App\Models\Persona;
use App\Models\Policia;
use App\Services\Personal\GestorLicencias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PoliciaController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Policia::class);

        $filtros = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'jerarquia_id' => ['nullable', 'integer'],
            'oficina_id' => ['nullable', 'integer'],
            'situacion' => ['nullable', 'string', 'max:30'],
        ]);

        $policias = Policia::query()
            ->with(['persona', 'jerarquia', 'oficina', 'funcion'])
            ->when($filtros['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('numero_legajo', 'like', "%{$search}%")
                    ->orWhereHas('persona', fn ($p) => $p->buscar($search));
            }))
            ->when($filtros['jerarquia_id'] ?? null, fn ($q, $id) => $q->where('jerarquia_id', $id))
            ->when($filtros['oficina_id'] ?? null, fn ($q, $id) => $q->where('oficina_id', $id))
            ->when($filtros['situacion'] ?? null, function ($q, $situacion) {
                $vigente = fn ($n) => $n->vigentesAl(today());
                $situacion === 'activo'
                    ? $q->whereDoesntHave('novedades', fn ($n) => $vigente($n)->where('situacion', '!=', 'activo'))
                    : $q->whereHas('novedades', fn ($n) => $vigente($n)->where('situacion', $situacion));
            })
            ->orderBy(Persona::select('apellido')->whereColumn('personas.id', 'policias.persona_id'))
            ->paginate(15)
            ->appends(request()->query());

        return view('personal.index', [
            'policias' => $policias,
            'jerarquias' => Jerarquia::orderBy('nombre')->get(),
            'oficinas' => Oficina::orderBy('nombre')->get(),
            'situaciones' => NovedadRevista::SITUACIONES,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Policia::class);

        return $this->formulario(new Policia(['estado' => 'activo']));
    }

    public function store(PoliciaRequest $request): RedirectResponse
    {
        Gate::authorize('create', Policia::class);

        $policia = DB::transaction(function () use ($request) {
            $persona = Persona::create($request->datosPersona());
            $policia = Policia::create($request->datosPolicia() + ['persona_id' => $persona->id]);
            $policia->sincronizarFuncionesAdicionales($request->validated('funciones_adicionales'));

            return $policia;
        });

        return redirect()->route('personal.show', $policia)->with('success', 'El agente fue registrado correctamente.');
    }

    public function show(Policia $policia): View
    {
        Gate::authorize('view', $policia);

        $policia->load(['persona', 'jerarquia', 'oficina', 'funcion', 'funcionesAdicionales', 'familiares',
            'novedades' => fn ($q) => $q->with(['usuario', 'licencia'])->orderByDesc('fecha_desde')->orderByDesc('id')]);
        $licencias = Licencia::query()->with(['tipo', 'reemplazo.persona'])->where('policia_id', $policia->id)->orderByDesc('fecha_inicio')->get();
        $anio = (int) request('anio', today()->year);

        return view('personal.show', [
            'policia' => $policia,
            'licencias' => $licencias,
            'situacionActual' => NovedadRevista::SITUACIONES[$policia->situacionActual()],
            'cupo' => GestorLicencias::resumen($policia, $anio),
            'situacionesManuales' => array_diff_key(NovedadRevista::SITUACIONES, ['licencia' => true]),
        ]);
    }

    public function edit(Policia $policia): View
    {
        Gate::authorize('update', $policia);

        return $this->formulario($policia->load(['persona', 'funcionesAdicionales']));
    }

    public function update(PoliciaRequest $request, Policia $policia): RedirectResponse
    {
        Gate::authorize('update', $policia);

        DB::transaction(function () use ($request, $policia) {
            $policia->persona->update($request->datosPersona());
            $policia->update($request->datosPolicia());
            $policia->sincronizarFuncionesAdicionales($request->validated('funciones_adicionales'));
        });

        return redirect()->route('personal.show', $policia)->with('success', 'El agente fue actualizado correctamente.');
    }

    public function destroy(Policia $policia): RedirectResponse
    {
        Gate::authorize('delete', $policia);

        // El legajo con historial no se borra: se da de baja con estado inactivo.
        if ($policia->licencias()->exists() || $policia->novedades()->exists() || $policia->usuario()->exists() || $policia->oficinasResponsable()->exists()) {
            return back()->withErrors(['policia' => 'El agente tiene historial o asignaciones; márquelo como inactivo en lugar de eliminarlo.']);
        }

        DB::transaction(function () use ($policia) {
            $persona = $policia->persona;
            $policia->delete();
            $persona?->delete();
        });

        return redirect()->route('personal.index')->with('success', 'El agente fue eliminado.');
    }

    private function formulario(Policia $policia): View
    {
        return view('personal.form', [
            'policia' => $policia,
            'persona' => $policia->persona ?? new Persona(),
            'jerarquias' => Jerarquia::orderBy('nombre')->get(),
            'funciones' => Funcion::orderBy('nombre')->get(),
            'oficinas' => Oficina::orderBy('nombre')->get(),
            'estadosCiviles' => Persona::ESTADOS_CIVILES,
        ]);
    }
}
