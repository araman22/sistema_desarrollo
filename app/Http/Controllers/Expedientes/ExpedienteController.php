<?php

namespace App\Http\Controllers\Expedientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expedientes\ExpedienteRequest;
use App\Models\Bibliorato;
use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Persona;
use App\Models\Policia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExpedienteController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Expediente::class);

        $filtros = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'bibliorato_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:30'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
        ]);

        // "pendiente_recepcion" no es un estado guardado: son los expedientes con un pase sin recibir.
        $expedientes = Expediente::query()
            ->with(['bibliorato', 'responsable.persona', 'paseEnCurso'])
            ->when($filtros['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhere('asunto', 'like', "%{$search}%")
                    ->orWhere('origen', 'like', "%{$search}%")
                    ->orWhere('destino', 'like', "%{$search}%");
            }))
            ->when($filtros['bibliorato_id'] ?? null, fn ($q, $id) => $q->where('bibliorato_id', $id))
            ->when($filtros['estado'] ?? null, fn ($q, $estado) => $estado === 'pendiente_recepcion' ? $q->pendientesDeRecepcion() : $q->where('estado', $estado))
            ->when($filtros['ubicacion'] ?? null, fn ($q, $ubicacion) => $q->where('ubicacion_actual', 'like', "%{$ubicacion}%"))
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends(request()->query());

        return view('expedientes.index', [
            'expedientes' => $expedientes,
            'biblioratos' => Bibliorato::orderBy('nombre')->get(),
            'estados' => Expediente::ESTADOS + ['pendiente_recepcion' => 'Pendiente de recepción'],
            'pendientes' => Expediente::pendientesDeRecepcion()->count(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Expediente::class);

        return $this->formulario(new Expediente(['estado' => 'en_tramite', 'fecha_emision' => today(), 'bibliorato_id' => request('bibliorato_id')]));
    }

    public function store(ExpedienteRequest $request): RedirectResponse
    {
        Gate::authorize('create', Expediente::class);

        $expediente = DB::transaction(function () use ($request) {
            $expediente = Expediente::create($request->safe()->except('documentos') + ['usuario_id' => Auth::id()]);
            $expediente->sincronizarDocumentos($request->validated('documentos'));

            return $expediente;
        });

        return redirect()->route('expedientes.show', $expediente)->with('success', 'El expediente fue registrado.');
    }

    public function show(Expediente $expediente): View
    {
        Gate::authorize('view', $expediente);

        $expediente->load(['bibliorato', 'responsable.persona', 'usuario', 'documentos.tipo', 'paseEnCurso',
            'pases' => fn ($q) => $q->with(['enviadoPor.persona', 'recibidoPor.persona', 'usuarioEnvio', 'usuarioRecepcion'])->orderBy('fecha_envio')->orderBy('id')]);

        return view('expedientes.show', ['expediente' => $expediente, 'agentes' => $this->agentes()]);
    }

    public function edit(Expediente $expediente): View
    {
        Gate::authorize('update', $expediente);

        return $this->formulario($expediente->load('documentos'));
    }

    public function update(ExpedienteRequest $request, Expediente $expediente): RedirectResponse
    {
        Gate::authorize('update', $expediente);

        DB::transaction(function () use ($request, $expediente) {
            $expediente->update($request->safe()->except('documentos'));
            $expediente->sincronizarDocumentos($request->validated('documentos'));
        });

        return redirect()->route('expedientes.show', $expediente)->with('success', 'El expediente fue actualizado.');
    }

    public function destroy(Expediente $expediente): RedirectResponse
    {
        Gate::authorize('delete', $expediente);
        if ($expediente->pases()->exists()) {
            return back()->withErrors(['expediente' => 'El expediente tiene pases registrados; archívelo en lugar de eliminarlo.']);
        }
        $expediente->delete();

        return redirect()->route('expedientes.index')->with('success', 'El expediente fue eliminado.');
    }

    private function formulario(Expediente $expediente): View
    {
        return view('expedientes.form', [
            'expediente' => $expediente,
            'biblioratos' => Bibliorato::query()->where('activo', true)->orWhere('id', $expediente->bibliorato_id)->orderBy('nombre')->get(),
            'agentes' => $this->agentes(),
            'documentos' => Documento::query()->with('tipo')->orderByDesc('id')->limit(500)->get(),
            'estados' => Expediente::ESTADOS,
        ]);
    }

    private function agentes()
    {
        return Policia::query()->with('persona')
            ->orderBy(Persona::select('apellido')->whereColumn('personas.id', 'policias.persona_id'))
            ->get();
    }
}
