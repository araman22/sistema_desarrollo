<?php

namespace App\Http\Controllers\Movilidad;

use App\Http\Controllers\Controller;
use App\Models\Bateria;
use App\Models\DroneRobot;
use App\Models\Policia;
use App\Services\Movilidad\GestionRecursos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

abstract class RecursoController extends Controller
{
    abstract protected function modelo(): string;

    abstract protected function modulo(): string;

    abstract protected function campos(): array;

    protected function recurso(Request $request): DroneRobot|Bateria
    {
        return $request->route($this->modulo() === 'drones' ? 'drone' : 'bateria');
    }

    protected function contexto(): array
    {
        return ['modelo' => $this->modelo(), 'modulo' => $this->modulo(), 'campos' => $this->campos(), 'titulo' => $this->modulo() === 'drones' ? 'Drones y robots' : 'Baterías'];
    }

    public function index(Request $request)
    {
        $modelo = $this->modelo();
        Gate::authorize('viewAny', $modelo);
        $datos = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', Rule::in($modelo::ESTADOS)],
            'tipo' => ['nullable', Rule::in(DroneRobot::TIPOS)],
            'dron_robot_id' => ['nullable', 'integer', 'exists:drones_robots,id'],
        ]);
        $query = $modelo::query();
        if (! empty($datos['q'])) {
            $query->where(fn ($q) => $q->where('codigo', 'like', '%'.$datos['q'].'%')->orWhere('numero_serie', 'like', '%'.$datos['q'].'%')->when($modelo === DroneRobot::class, fn ($q) => $q->orWhere('nombre', 'like', '%'.$datos['q'].'%')));
        }
        if (! empty($datos['estado'])) {
            $query->where('estado', $datos['estado']);
        }
        if ($modelo === DroneRobot::class) {
            $query->with('responsable.persona');
            if (! empty($datos['tipo'])) {
                $query->where('tipo', $datos['tipo']);
            }
        } else {
            $query->with('droneRobot');
            if (! empty($datos['dron_robot_id'])) {
                $query->where('dron_robot_id', $datos['dron_robot_id']);
            }
        }

        return view('movilidad.recursos.index', $this->contexto() + ['recursos' => $query->orderBy('codigo')->paginate(20)->withQueryString()]);
    }

    public function create()
    {
        $modelo = $this->modelo();
        Gate::authorize('create', $modelo);

        return $this->formulario(new $modelo);
    }

    public function show(Request $request)
    {
        $recurso = $this->recurso($request);
        Gate::authorize('view', $recurso);
        $recurso->load($recurso instanceof DroneRobot ? 'responsable.persona' : 'droneRobot');
        $historial = Gate::allows('movilidad.mantenimiento.viewAny', $recurso)
            ? $recurso->mantenimientos()->with('responsable.persona')->orderByDesc('fecha')->orderByDesc('id')->paginate(10) : null;
        $baterias = $recurso instanceof DroneRobot && Gate::allows('viewAny', Bateria::class) ? $recurso->baterias()->orderBy('codigo')->get() : null;

        return view('movilidad.recursos.show', $this->contexto() + compact('recurso', 'historial', 'baterias'));
    }

    public function edit(Request $request)
    {
        $recurso = $this->recurso($request);
        Gate::authorize('update', $recurso);

        return $this->formulario($recurso);
    }

    public function destroy(Request $request, GestionRecursos $gestion)
    {
        $recurso = $this->recurso($request);
        Gate::authorize('delete', $recurso);
        $gestion->eliminar($recurso);

        return redirect()->route('movilidad.'.$this->modulo().'.index')->with('status', 'Recurso eliminado.');
    }

    protected function formulario(DroneRobot|Bateria $recurso)
    {
        return view('movilidad.recursos.form', $this->contexto() + [
            'recurso' => $recurso,
            'responsables' => $recurso instanceof DroneRobot ? Policia::with('persona')->orderBy('numero_legajo')->get() : collect(),
            'drones' => $recurso instanceof Bateria ? DroneRobot::orderBy('codigo')->get() : collect(),
        ]);
    }
}
