<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehiculoDocumentoRequest;
use App\Models\Vehiculo;
use App\Models\VehiculoDocumento;
use App\Services\MovilidadAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class VehiculoDocumentoController extends Controller
{
    public function store(VehiculoDocumentoRequest $r, Vehiculo $vehiculo)
    {
        Gate::authorize('update', $vehiculo);
        $archivo = $r->file('archivo');
        $ruta = $archivo->store('vehiculos', 'local');
        try {
            DB::transaction(function () use ($archivo, $ruta, $vehiculo) {
                $v = Vehiculo::lockForUpdate()->findOrFail($vehiculo->id);
                $d = $v->documentos()->create(['nombre' => basename($archivo->getClientOriginalName()), 'ruta' => $ruta, 'mime' => $archivo->getMimeType()]);
                MovilidadAuditoria::registrar('crear', $d);
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($ruta);
            throw $e;
        }

        return back()->with('success', 'Documento adjuntado.');
    }

    public function download(Vehiculo $vehiculo, VehiculoDocumento $documento)
    {
        Gate::authorize('view', $vehiculo);
        abort_unless(Storage::disk('local')->exists($documento->ruta), 404);

        return Storage::disk('local')->download($documento->ruta, 'documento-'.$documento->id.'.'.pathinfo($documento->ruta, PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff']);
    }
}
