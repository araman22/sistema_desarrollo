<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\StoreRolRequest;
use App\Http\Requests\Roles\UpdateRolRequest;
use App\Models\Auditoria;
use App\Models\Permiso;
use App\Models\Rol;
use App\Policies\RolPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Rol::class);

        $roles = Rol::query()
            ->withCount('usuarios', 'permisos')
            ->when($request->filled('q'), fn ($query) => $query->where('nombre', 'like', '%'.$request->string('q')->trim().'%'))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('roles.index', ['roles' => $roles]);
    }

    public function create(): View
    {
        Gate::authorize('create', Rol::class);

        return view('roles.create', ['permisosPorModulo' => $this->permisosPorModulo()]);
    }

    public function store(StoreRolRequest $request): RedirectResponse
    {
        Gate::authorize('create', Rol::class);

        $datos = $request->validated();

        $rol = DB::transaction(function () use ($datos) {
            $rol = Rol::create(Arr::except($datos, 'permisos'));

            if (array_key_exists('permisos', $datos)) {
                $this->sincronizarPermisos($rol, $datos['permisos'] ?? []);
            }

            return $rol;
        });

        return redirect()->route('roles.index')
            ->with('success', "Rol {$rol->nombre} creado.");
    }

    public function show(Rol $rol): View
    {
        Gate::authorize('view', $rol);

        $rol->loadCount('usuarios');

        return view('roles.show', [
            'rol' => $rol,
            'permisosPorModulo' => $rol->permisos()->orderBy('modulo')->orderBy('accion')->get()->groupBy('modulo'),
        ]);
    }

    public function edit(Rol $rol): View
    {
        Gate::authorize('update', $rol);

        return view('roles.edit', [
            'rol' => $rol,
            'permisosPorModulo' => $this->permisosPorModulo(),
            'permisosDelRol' => $rol->permisos()->pluck('permisos.id')->all(),
        ]);
    }

    public function update(UpdateRolRequest $request, Rol $rol): RedirectResponse
    {
        Gate::authorize('update', $rol);

        $datos = $request->validated();

        DB::transaction(function () use ($rol, $datos) {
            // Al administrador nunca se le tocan los permisos.
            if (array_key_exists('permisos', $datos) && ! RolPolicy::esAdministrador($rol)) {
                $this->sincronizarPermisos($rol, $datos['permisos'] ?? []);
            }

            $rol->update(Arr::except($datos, 'permisos'));
        });

        return redirect()->route('roles.index')
            ->with('success', "Rol {$rol->nombre} actualizado.");
    }

    public function cambiarEstado(Rol $rol): RedirectResponse
    {
        Gate::authorize('cambiarEstado', $rol);

        $rol->update(['activo' => ! $rol->activo]);

        if ($rol->activo) {
            return back()->with('success', "Rol {$rol->nombre} activado.");
        }

        $cantidadUsuarios = $rol->usuarios()->count();

        $mensaje = $cantidadUsuarios > 0
            ? "Rol {$rol->nombre} desactivado. Sus {$cantidadUsuarios} usuario(s) perdieron los permisos del rol hasta que se reactive o se los reasigne."
            : "Rol {$rol->nombre} desactivado.";

        return back()->with('success', $mensaje);
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        Gate::authorize('delete', $rol);

        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', "Rol {$rol->nombre} eliminado.");
    }

    /**
     * @return Collection<string, \Illuminate\Database\Eloquent\Collection<int, Permiso>>
     */
    private function permisosPorModulo(): Collection
    {
        return Permiso::orderBy('modulo')->orderBy('accion')->get()->groupBy('modulo')->toBase();
    }

    /**
     * El sync de rol_permiso no dispara eventos de modelo, así que el cambio
     * se audita a mano con los mismos campos que RegistraAuditoria.
     *
     * @param  array<int, int|string>  $idsPermisos
     */
    private function sincronizarPermisos(Rol $rol, array $idsPermisos): void
    {
        $antes = $this->nombresPermisos($rol);

        $rol->permisos()->sync($idsPermisos);

        $despues = $this->nombresPermisos($rol);

        if ($antes === $despues) {
            return;
        }

        Auditoria::create([
            'usuario_id' => Auth::id(),
            'accion' => 'editar',
            'tabla_afectada' => 'rol_permiso',
            'registro_id' => $rol->getKey(),
            'valor_anterior' => ['permisos' => $antes],
            'valor_nuevo' => ['permisos' => $despues],
            'direccion_ip' => request()->ip(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function nombresPermisos(Rol $rol): array
    {
        return $rol->permisos()->orderBy('nombre')->pluck('nombre')->all();
    }
}
