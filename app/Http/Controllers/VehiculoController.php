<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehiculoRequest;
use App\Models\Policia;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Services\MovilidadAuditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VehiculoController extends Controller
{
    public function index(Request $r)
    {
        Gate::authorize('viewAny', Vehiculo::class);
        $f = $r->validate(['q' => 'nullable|string|max:100', 'estado' => 'nullable|in:disponible,en_uso,mantenimiento,fuera_servicio,baja']);
        $vehiculos = Vehiculo::with(['tipo', 'mantenimientos'])->when($f['q'] ?? null, fn ($q, $s) => $q->where('dominio', 'like', '%'.$s.'%'))
            ->when($f['estado'] ?? null, fn ($q, $s) => $q->where('estado', $s))->orderBy('dominio')->paginate(15)->withQueryString();

        return view('movilidad.vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        Gate::authorize('create', Vehiculo::class);

        return $this->form(new Vehiculo(['estado' => 'disponible', 'kilometraje' => 0]));
    }

    public function edit(Vehiculo $vehiculo)
    {
        Gate::authorize('update', $vehiculo);

        return $this->form($vehiculo);
    }

    private function form(Vehiculo $vehiculo)
    {
        return view('movilidad.vehiculos.form', ['vehiculo' => $vehiculo, 'tipos' => TipoVehiculo::orderBy('nombre')->get(), 'responsables' => Policia::opcionesResponsable()]);
    }

    public function show(Vehiculo $vehiculo)
    {
        Gate::authorize('view', $vehiculo);
        $vehiculo->load(['tipo', 'responsable.persona', 'mantenimientos', 'documentos']);

        return view('movilidad.vehiculos.show', compact('vehiculo'));
    }

    public function store(VehiculoRequest $r)
    {
        $v = DB::transaction(function () use ($r) {
            TipoVehiculo::lockForUpdate()->findOrFail($r->integer('tipo_vehiculo_id'));
            $v = Vehiculo::create($r->validated());
            MovilidadAuditoria::registrar('crear', $v);

            return $v;
        });

        return redirect()->route('vehiculos.show', $v)->with('success', 'Vehículo registrado.');
    }

    public function update(VehiculoRequest $r, Vehiculo $vehiculo)
    {
        DB::transaction(function () use ($r, $vehiculo) {
            $v = Vehiculo::lockForUpdate()->findOrFail($vehiculo->id);
            if ($r->integer('kilometraje') < $v->kilometraje) {
                throw ValidationException::withMessages(['kilometraje' => 'El kilometraje no puede disminuir.']);
            }
            TipoVehiculo::lockForUpdate()->findOrFail($r->integer('tipo_vehiculo_id'));
            $anterior = $v->getAttributes();
            $v->update($r->validated());
            MovilidadAuditoria::registrar('editar', $v, $anterior);
        });

        return redirect()->route('vehiculos.show', $vehiculo)->with('success', 'Vehículo actualizado.');
    }

    public function destroy(Vehiculo $vehiculo)
    {
        Gate::authorize('delete', $vehiculo);
        DB::transaction(function () use ($vehiculo) {
            $v = Vehiculo::lockForUpdate()->findOrFail($vehiculo->id);
            if ($v->mantenimientos()->exists() || $v->documentos()->exists()) {
                throw ValidationException::withMessages(['vehiculo' => 'Tiene historial o documentos: cambie su estado a baja para conservarlos.']);
            }
            MovilidadAuditoria::registrar('eliminar', $v, $v->getAttributes());
            $v->delete();
        });

        return redirect()->route('vehiculos.index')->with('success','Vehículo eliminado.');
    }
}
