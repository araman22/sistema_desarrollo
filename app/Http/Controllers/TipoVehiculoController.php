<?php

namespace App\Http\Controllers;

use App\Http\Requests\TipoVehiculoRequest;
use App\Models\TipoVehiculo;
use App\Services\MovilidadAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TipoVehiculoController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', TipoVehiculo::class);

        return view('movilidad.tipos.index', ['tipos' => TipoVehiculo::withCount('vehiculos')->orderBy('nombre')->paginate(20)]);
    }

    public function create()
    {
        Gate::authorize('create', TipoVehiculo::class);

        return view('movilidad.tipos.form', ['tipo' => new TipoVehiculo]);
    }

    public function edit(TipoVehiculo $tipo)
    {
        Gate::authorize('update', $tipo);

        return view('movilidad.tipos.form', compact('tipo'));
    }

    public function show(TipoVehiculo $tipo)
    {
        Gate::authorize('view', $tipo);

        return view('movilidad.tipos.show', compact('tipo'));
    }

    public function store(TipoVehiculoRequest $r)
    {
        Gate::authorize('create', TipoVehiculo::class);
        $data = $r->validated();
        DB::transaction(function () use ($data) {
            $t = TipoVehiculo::create($data);
            MovilidadAuditoria::registrar('crear', $t);
        });

        return redirect()->route('tipos-vehiculos.index')->with('success', 'Tipo registrado.');
    }

    public function update(TipoVehiculoRequest $r, TipoVehiculo $tipo)
    {
        Gate::authorize('update', $tipo);
        $data = $r->validated();
        DB::transaction(function () use ($data, $tipo) {
            $anterior = $tipo->getAttributes();
            $tipo->update($data);
            MovilidadAuditoria::registrar('editar', $tipo, $anterior);
        });

        return redirect()->route('tipos-vehiculos.index')->with('success', 'Tipo actualizado.');
    }

    public function destroy(TipoVehiculo $tipo)
    {
        Gate::authorize('delete', $tipo);
        DB::transaction(function () use ($tipo) {
            $t = TipoVehiculo::lockForUpdate()->findOrFail($tipo->id);
            if ($t->vehiculos()->exists()) {
                throw ValidationException::withMessages(['tipo' => 'No puede eliminar un tipo utilizado por vehículos.']);
            }MovilidadAuditoria::registrar('eliminar', $t, $t->getAttributes());
            $t->delete();
        });

        return redirect()->route('tipos-vehiculos.index')->with('success','Tipo eliminado.');
    }
}
