<?php

namespace App\Http\Controllers\Movilidad;

use App\Http\Requests\Movilidad\BateriaRequest;
use App\Models\Bateria;
use App\Services\Movilidad\GestionRecursos;

class BateriaController extends RecursoController
{
    protected function modelo(): string
    {
        return Bateria::class;
    }

    protected function modulo(): string
    {
        return 'baterias';
    }

    protected function campos(): array
    {
        return ['dron_robot_id' => 'Dron / robot', 'codigo' => 'Código', 'marca' => 'Marca', 'modelo' => 'Modelo', 'numero_serie' => 'Número de serie', 'capacidad' => 'Capacidad (incluí la unidad)', 'ciclos' => 'Ciclos acumulados', 'estado' => 'Estado', 'fecha_adquisicion' => 'Fecha de adquisición', 'ultima_revision' => 'Última revisión', 'observaciones' => 'Observaciones'];
    }

    public function store(BateriaRequest $request, GestionRecursos $gestion)
    {
        $bateria = $gestion->bateria($request->validated());

        return redirect()->route('movilidad.baterias.show', $bateria)->with('status', 'Batería registrada.');
    }

    public function update(BateriaRequest $request, Bateria $bateria, GestionRecursos $gestion)
    {
        $gestion->bateria($request->validated(), $bateria);

        return redirect()->route('movilidad.baterias.show', $bateria)->with('status', 'Batería actualizada.');
    }
}
