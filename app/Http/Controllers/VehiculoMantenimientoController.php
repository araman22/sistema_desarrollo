<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehiculoMantenimientoRequest;
use App\Models\ResponsablePolicial;
use App\Models\Vehiculo;
use App\Models\VehiculoMantenimiento;
use App\Services\MovilidadAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VehiculoMantenimientoController extends Controller
{
    public function create(Vehiculo $vehiculo)
    {
        Gate::authorize('view', $vehiculo);
        Gate::authorize('create', VehiculoMantenimiento::class);

        return $this->form($vehiculo, new VehiculoMantenimiento(['fecha' => today(), 'kilometraje' => $vehiculo->kilometraje, 'tipo' => 'preventivo']));
    }

    public function edit(Vehiculo $vehiculo, VehiculoMantenimiento $mantenimiento)
    {
        Gate::authorize('view', $vehiculo);
        Gate::authorize('update', $mantenimiento);

        return $this->form($vehiculo, $mantenimiento);
    }

    private function form(Vehiculo $vehiculo, VehiculoMantenimiento $mantenimiento)
    {
        return view('movilidad.mantenimientos.form', ['vehiculo' => $vehiculo, 'mantenimiento' => $mantenimiento, 'responsables' => ResponsablePolicial::opciones()]);
    }

    public function store(VehiculoMantenimientoRequest $r, Vehiculo $vehiculo)
    {
        $this->guardar($r, $vehiculo);

        return redirect()->route('vehiculos.show', $vehiculo)->with('success', 'Mantenimiento registrado.');
    }

    public function update(VehiculoMantenimientoRequest $r, Vehiculo $vehiculo, VehiculoMantenimiento $mantenimiento)
    {
        $this->guardar($r, $vehiculo, $mantenimiento);

        return redirect()->route('vehiculos.show', $vehiculo)->with('success', 'Mantenimiento actualizado.');
    }

    private function guardar(VehiculoMantenimientoRequest $r, Vehiculo $vehiculo, ?VehiculoMantenimiento $m = null): void
    {
        DB::transaction(function () use ($r, $vehiculo, $m) {
            $v = Vehiculo::lockForUpdate()->findOrFail($vehiculo->id);
            if ($v->estado === 'baja') {
                throw ValidationException::withMessages(['vehiculo' => 'No se registran mantenimientos sobre un vehículo dado de baja.']);
            }
            $data = $r->validated();
            if ($m) {
                $m = $v->mantenimientos()->lockForUpdate()->findOrFail($m->id);
            }
            $fecha = $data['fecha'];
            $km = (int) $data['kilometraje'];
            $otros = $v->mantenimientos()->when($m, fn ($q) => $q->where('id', '!=', $m->id));
            $id = $m?->id ?? PHP_INT_MAX;
            $previos = (clone $otros)->where(function ($q) use ($fecha, $id) {
                $q->whereDate('fecha', '<', $fecha)->orWhere(fn ($q) => $q->whereDate('fecha', $fecha)->where('id', '<', $id));
            });
            $posteriores = (clone $otros)->where(function ($q) use ($fecha, $id) {
                $q->whereDate('fecha', '>', $fecha)->orWhere(fn ($q) => $q->whereDate('fecha', $fecha)->where('id', '>', $id));
            });
            if ($previos->where('kilometraje', '>', $km)->exists() || $posteriores->where('kilometraje', '<', $km)->exists()) {
                throw ValidationException::withMessages(['kilometraje' => 'El kilometraje contradice el historial de mantenimiento.']);
            }
            $anterior = $m?->getAttributes();
            if ($m) {
                $m->update($data);
            } else {
                $m = $v->mantenimientos()->create($data);
            }
            MovilidadAuditoria::registrar($anterior ? 'editar' : 'crear', $m, $anterior);
            if ($km > $v->kilometraje) {
                $antes = $v->getAttributes();
                $v->update(['kilometraje' => $km]);
                MovilidadAuditoria::registrar('editar', $v, $antes);
            }
        });
    }

    public function destroy(Vehiculo $vehiculo,VehiculoMantenimiento $mantenimiento)
    {
        Gate::authorize('view',$vehiculo);
        Gate::authorize('delete',$mantenimiento);
        abort(409,'El historial se conserva. Corrija el mantenimiento mediante edición.');
    }
}
