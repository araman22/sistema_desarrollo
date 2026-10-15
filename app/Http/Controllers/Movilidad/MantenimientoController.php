<?php

namespace App\Http\Controllers\Movilidad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Movilidad\MantenimientoRequest;
use App\Models\Mantenimiento;
use App\Models\Policia;
use App\Services\Movilidad\GestionMantenimientos;
use App\Support\RecursoMantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MantenimientoController extends Controller
{
    public function index(Request $request)
    {
        $recurso = RecursoMantenimiento::desde($request);
        Gate::authorize('movilidad.mantenimiento.viewAny', $recurso);

        return view('movilidad.mantenimientos.index', [
            'recurso' => $recurso, 'prefijo' => RecursoMantenimiento::prefijo($recurso),
            'mantenimientos' => $recurso->mantenimientos()->with('responsable.persona')->orderByDesc('fecha')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function create(Request $request)
    {
        $recurso = RecursoMantenimiento::desde($request);
        Gate::authorize('movilidad.mantenimiento.create', $recurso);

        return $this->form($request, new Mantenimiento(['tipo' => 'revision', 'estado' => 'programado', 'fecha' => today()]));
    }

    public function store(MantenimientoRequest $request, GestionMantenimientos $gestion)
    {
        $recurso = RecursoMantenimiento::desde($request);
        $gestion->guardar($recurso, $request->validated());

        return redirect()->route(RecursoMantenimiento::prefijo($recurso).'index', $recurso)->with('status', 'Mantenimiento registrado.');
    }

    public function show(Request $request)
    {
        $mantenimiento = RecursoMantenimiento::registro($request);
        Gate::authorize('movilidad.mantenimiento.view', $mantenimiento);

        return view('movilidad.mantenimientos.show', ['recurso' => RecursoMantenimiento::desde($request), 'mantenimiento' => $mantenimiento->load('responsable.persona'), 'prefijo' => RecursoMantenimiento::prefijo(RecursoMantenimiento::desde($request))]);
    }

    public function edit(Request $request)
    {
        $mantenimiento = RecursoMantenimiento::registro($request);
        Gate::authorize('movilidad.mantenimiento.update', $mantenimiento);

        return $this->form($request, $mantenimiento);
    }

    public function update(MantenimientoRequest $request, GestionMantenimientos $gestion)
    {
        $recurso = RecursoMantenimiento::desde($request);
        $gestion->guardar($recurso, $request->validated(), RecursoMantenimiento::registro($request));

        return redirect()->route(RecursoMantenimiento::prefijo($recurso).'index', $recurso)->with('status', 'Mantenimiento actualizado.');
    }

    public function destroy(Request $request, GestionMantenimientos $gestion)
    {
        $recurso = RecursoMantenimiento::desde($request);
        $mantenimiento = RecursoMantenimiento::registro($request);
        Gate::authorize('movilidad.mantenimiento.delete', $mantenimiento);
        $gestion->eliminar($recurso, $mantenimiento);

        return redirect()->route(RecursoMantenimiento::prefijo($recurso).'index', $recurso)->with('status', 'Programación eliminada.');
    }

    private function form(Request $request, Mantenimiento $mantenimiento)
    {
        $recurso = RecursoMantenimiento::desde($request);

        return view('movilidad.mantenimientos.form', ['recurso' => $recurso, 'mantenimiento' => $mantenimiento, 'prefijo' => RecursoMantenimiento::prefijo($recurso), 'responsables' => Policia::with('persona')->orderBy('numero_legajo')->get()]);
    }
}
