<?php

namespace App\Http\Controllers;

use App\Http\Requests\Usuarios\StoreUsuarioRequest;
use App\Http\Requests\Usuarios\UpdateUsuarioRequest;
use App\Models\Policia;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Usuario::class);

        $usuarios = Usuario::query()
            ->with('rol')
            ->when($request->filled('q'), function ($query) use ($request) {
                $busqueda = '%'.$request->string('q')->trim().'%';

                $query->where(function ($query) use ($busqueda) {
                    $query->where('nombre_usuario', 'like', $busqueda)
                        ->orWhere('correo_electronico', 'like', $busqueda);
                });
            })
            ->when($request->filled('rol_id'), fn ($query) => $query->where('rol_id', $request->integer('rol_id')))
            ->when($request->filled('activo'), fn ($query) => $query->where('activo', $request->boolean('activo')))
            ->orderBy('nombre_usuario')
            ->paginate(15)
            ->withQueryString();

        return view('usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('nombre')->get(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Usuario::class);

        return view('usuarios.create', [
            'roles' => $this->rolesActivos(),
            'policias' => $this->policiasDisponibles(),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        Gate::authorize('create', Usuario::class);

        $usuario = Usuario::create($request->validated());

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre_usuario} creado.");
    }

    public function show(Usuario $usuario): View
    {
        Gate::authorize('view', $usuario);

        $usuario->load(['rol', 'policia.persona']);

        return view('usuarios.show', ['usuario' => $usuario]);
    }

    public function edit(Usuario $usuario): View
    {
        Gate::authorize('update', $usuario);

        return view('usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $this->rolesActivos(),
            'policias' => $this->policiasDisponibles($usuario),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        Gate::authorize('update', $usuario);

        $datos = $request->validated();

        // Contraseña vacía: se conserva la actual.
        if (blank($datos['contrasena'] ?? null)) {
            unset($datos['contrasena']);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre_usuario} actualizado.");
    }

    public function cambiarEstado(Usuario $usuario): RedirectResponse
    {
        Gate::authorize('cambiarEstado', $usuario);

        $usuario->update(['activo' => ! $usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$usuario->nombre_usuario} {$estado}.");
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        Gate::authorize('delete', $usuario);

        if ($usuario->auditorias()->exists()) {
            return back()->with('error', 'El usuario tiene historial registrado. Desactivalo en lugar de eliminarlo.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->nombre_usuario} eliminado.");
    }

    /**
     * @return Collection<int, Rol>
     */
    private function rolesActivos(): Collection
    {
        return Rol::where('activo', true)->orderBy('nombre')->get();
    }

    /**
     * Policías sin usuario asignado, más el del usuario que se edita.
     *
     * @return Collection<int, Policia>
     */
    private function policiasDisponibles(?Usuario $usuario = null): Collection
    {
        return Policia::with('persona')
            ->whereDoesntHave('usuario')
            ->when($usuario?->policia_id, fn ($query, $policiaId) => $query->orWhere('id', $policiaId))
            ->orderBy('numero_legajo')
            ->get();
    }
}
