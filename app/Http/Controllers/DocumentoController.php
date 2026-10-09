<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentoRequest;
use App\Models\Documento;
use App\Models\TipoDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentoController extends Controller
{
    public function index(): View
    {
        $query = Documento::query()->with('tipo');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('numero', 'like', "%{$search}%");
            });
        }

        if ($tipoId = request('tipo_documento_id')) {
            $query->where('tipo_documento_id', $tipoId);
        }

        $documentos = $query->orderBy('nombre')->paginate(10)->appends(request()->query());
        $tipos = TipoDocumento::query()->orderBy('nombre')->get();

        return view('documentos.index', compact('documentos', 'tipos'));
    }

    public function create(): View
    {
        return view('documentos.form', [
            'documento' => new Documento(),
            'tipos' => TipoDocumento::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(DocumentoRequest $request): RedirectResponse
    {
        $archivo = $request->file('archivo');
        $this->validarArchivo($archivo);

        $documento = Documento::create([
            'tipo_documento_id' => $request->input('tipo_documento_id'),
            'nombre' => $request->input('nombre'),
            'numero' => (int) $request->input('numero'),
            'descripcion' => $request->input('descripcion'),
            'ruta_archivo' => $this->guardarArchivo($archivo),
            'mime_type' => mime_content_type($archivo->getRealPath()),
            'tamano_bytes' => $archivo->getSize(),
            'extension' => strtolower($archivo->getClientOriginalExtension()),
            'archivo_original' => $archivo->getClientOriginalName(),
            'hash_archivo' => hash_file('sha256', $archivo->getRealPath()),
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('documentos.show', $documento)
            ->with('success', 'El documento fue cargado correctamente.');
    }

    public function show(Documento $documento): View
    {
        $documento->load(['tipo', 'usuario']);

        return view('documentos.show', compact('documento'));
    }

    public function edit(Documento $documento): View
    {
        return view('documentos.form', [
            'documento' => $documento,
            'tipos' => TipoDocumento::query()->orderBy('nombre')->get(),
        ]);
    }

    public function update(DocumentoRequest $request, Documento $documento): RedirectResponse
    {
        $datos = [
            'tipo_documento_id' => $request->input('tipo_documento_id'),
            'nombre' => $request->input('nombre'),
            'numero' => (int) $request->input('numero'),
            'descripcion' => $request->input('descripcion'),
        ];

        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            $this->validarArchivo($archivo);

            if ($documento->ruta_archivo && Storage::disk('local')->exists($documento->ruta_archivo)) {
                Storage::disk('local')->delete($documento->ruta_archivo);
            }

            $datos['ruta_archivo'] = $this->guardarArchivo($archivo);
            $datos['mime_type'] = mime_content_type($archivo->getRealPath());
            $datos['tamano_bytes'] = $archivo->getSize();
            $datos['extension'] = strtolower($archivo->getClientOriginalExtension());
            $datos['archivo_original'] = $archivo->getClientOriginalName();
            $datos['hash_archivo'] = hash_file('sha256', $archivo->getRealPath());
        }

        $documento->update($datos);

        return redirect()->route('documentos.show', $documento)
            ->with('success', 'El documento fue actualizado correctamente.');
    }

    public function destroy(Documento $documento): RedirectResponse
    {
        if ($documento->ruta_archivo && Storage::disk('local')->exists($documento->ruta_archivo)) {
            Storage::disk('local')->delete($documento->ruta_archivo);
        }

        $documento->delete();

        return redirect()->route('documentos.index')
            ->with('success', 'El documento fue eliminado correctamente.');
    }

    public function download(Documento $documento)
    {
        if (! $documento->ruta_archivo || ! Storage::disk('local')->exists($documento->ruta_archivo)) {
            abort(404, 'El archivo ya no está disponible.');
        }

        return response()->download(
            Storage::disk('local')->path($documento->ruta_archivo),
            $documento->archivo_original ?: ($documento->nombre.'.'.$documento->extension),
        );
    }

    protected function guardarArchivo(UploadedFile $archivo): string
    {
        $ruta = 'documentos/'.date('Y/m');
        $nombre = $archivo->hashName();

        return $archivo->storeAs($ruta, $nombre, 'local');
    }

    protected function validarArchivo(?UploadedFile $archivo): void
    {
        if (! $archivo instanceof UploadedFile) {
            abort(422, 'Debe adjuntar un archivo válido.');
        }

        $mimeReal = mime_content_type($archivo->getRealPath());
        $ext = strtolower($archivo->getClientOriginalExtension());
        $permitidos = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'text/plain',
        ];

        $extPermitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];

        if (! in_array($mimeReal, $permitidos, true) || ! in_array($ext, $extPermitidas, true)) {
            abort(422, 'El tipo de archivo no está permitido.');
        }

        if ($archivo->getSize() > 5 * 1024 * 1024) {
            abort(422, 'El archivo supera el tamaño máximo permitido (5 MB).');
        }
    }
}
