<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class InicializacionLocalSeeder extends Seeder
{
    // Copia intencional de RolSeeder::ROLES, verificada por una prueba de paridad.
    // Evita ejecutar su updateOrCreate/sync o modificar archivos del Grupo 1.
    private const ROLES = [
        Rol::ADMINISTRADOR => [
            'descripcion' => 'Acceso total a todas las funcionalidades del sistema.',
            'permisos' => '*',
        ],
        'encargado_oficina' => [
            'descripcion' => 'Gestiona policias, oficinas, proyectos y aprobaciones.',
            'permisos' => [
                'policias' => ['ver', 'crear', 'editar', 'exportar'],
                'oficinas' => ['ver', 'editar'],
                'proyectos' => '*',
                'licencias' => '*',
                'capacitaciones' => '*',
                'actividades' => '*',
                'incidencias' => ['ver', 'crear', 'editar', 'resolver'],
                'movimientos_economicos' => ['ver', 'exportar'],
                'auditorias' => ['ver'],
                'alertas' => ['ver', 'atender'],
            ],
        ],
        'soporte' => [
            'descripcion' => 'Administra equipamiento, inventario y mantenimientos tecnologicos.',
            'permisos' => [
                'equipamientos' => '*',
                'inventarios' => '*',
                'mantenimientos' => '*',
                'drones_robots' => '*',
                'baterias' => '*',
                'incidencias' => ['ver', 'crear', 'editar', 'resolver'],
            ],
        ],
        'administrativo' => [
            'descripcion' => 'Registra movimientos economicos, licencias, vehiculos y documentos.',
            'permisos' => [
                'movimientos_economicos' => ['ver', 'crear', 'editar', 'exportar'],
                'licencias' => ['ver', 'crear', 'editar'],
                'vehiculos' => ['ver', 'crear', 'editar'],
                'vehiculo_mantenimientos' => ['ver', 'crear', 'editar'],
                'documentos' => ['ver', 'crear', 'editar', 'descargar'],
                'policias' => ['ver'],
            ],
        ],
        'invitado' => [
            'descripcion' => 'Solo lectura sobre los modulos del sistema.',
            'permisos' => [
                'policias' => ['ver'],
                'oficinas' => ['ver'],
                'proyectos' => ['ver'],
                'actividades' => ['ver'],
                'inventarios' => ['ver'],
                'capacitaciones' => ['ver'],
            ],
        ],
    ];

    private const CAJA_CHICA = [
        'administrador' => ['ver', 'crear', 'descargar'],
        'administrativo' => ['ver', 'crear', 'descargar'],
        'encargado_oficina' => ['ver', 'descargar'],
    ];

    public function run(): void
    {
        $conexion = DB::connection();
        $this->validarDestino($conexion);

        if (! $this->command || $this->command->option('no-interaction')) {
            throw new RuntimeException('La inicializacion requiere una consola interactiva.');
        }

        $nombre = $this->command->ask('Nombre del administrador local');
        $correo = $this->command->ask('Correo del administrador local');
        $validacion = Validator::make([
            'nombre' => $nombre,
            'correo' => $correo,
        ], [
            'nombre' => ['required', 'string', 'max:100', 'alpha_dash'],
            'correo' => ['required', 'email', 'max:150'],
        ]);

        if ($validacion->fails()) {
            throw new RuntimeException('Nombre o correo del administrador no valido.');
        }

        $this->conBloqueo($conexion, function () use ($conexion, $nombre, $correo) {
            $conexion->transaction(function () use ($nombre, $correo) {
                $this->inicializar($nombre, $correo);
            });
        });

        $this->command->info('Inicializacion local verificada. Permisos de caja chica disponibles.');
    }

    protected function validarDestino(Connection $conexion): void
    {
        // ConnectionFactory elimina read/write de la configuracion resuelta.
        $configuracion = config('database.connections.'.$conexion->getConfig('name'), []);

        if (! app()->environment('local')
            || $conexion->getConfig('driver') !== 'mysql'
            || $conexion->getConfig('host') !== '127.0.0.1'
            || $conexion->getConfig('database') !== 'desarrollo_sistema'
            || filled($conexion->getConfig('unix_socket'))
            || isset($configuracion['read'])
            || isset($configuracion['write'])
            || $conexion->getConfig('read') !== null
            || $conexion->getConfig('write') !== null) {
            throw new RuntimeException('Destino rechazado: requiere entorno local, MySQL en 127.0.0.1 y base desarrollo_sistema, sin socket ni replicas.');
        }
    }

    protected function conBloqueo(Connection $conexion, Closure $operacion): void
    {
        $nombre = 'sigdet:desarrollo_sistema:inicializacion_local';
        $resultado = $conexion->selectOne('SELECT GET_LOCK(?, 0) AS adquirido', [$nombre]);

        if ((int) ($resultado->adquirido ?? 0) !== 1) {
            throw new RuntimeException('Ya hay una inicializacion local en curso.');
        }

        try {
            $operacion();
        } finally {
            $conexion->selectOne('SELECT RELEASE_LOCK(?) AS liberado', [$nombre]);
        }
    }

    private function inicializar(string $nombre, string $correo): void
    {
        $existentes = Rol::all()->filter(fn ($rol) => in_array(mb_strtolower(trim($rol->nombre)), array_keys(self::ROLES), true));

        if ($existentes->contains(fn ($rol) => ! array_key_exists($rol->nombre, self::ROLES))) {
            throw new RuntimeException('Hay nombres de roles institucionales ambiguos; no se modificaron.');
        }

        if ($existentes->groupBy('nombre')->contains(fn ($roles) => $roles->count() > 1)) {
            throw new RuntimeException('Hay roles institucionales duplicados; no se modificaron.');
        }

        $porNombre = Usuario::where('nombre_usuario', $nombre)->first();
        $porCorreo = Usuario::where('correo_electronico', $correo)->first();
        $rolAdmin = $existentes->firstWhere('nombre', Rol::ADMINISTRADOR);

        if (($porCorreo && (! $porNombre || ! $porCorreo->is($porNombre)))
            || ($porNombre && ($porNombre->nombre_usuario !== $nombre
                || $porNombre->correo_electronico !== $correo
                || ! $porNombre->activo
                || ! $rolAdmin
                || $porNombre->rol_id !== $rolAdmin->id))
            || ($rolAdmin && ! $rolAdmin->activo)) {
            throw new RuntimeException('Conflicto con usuario, correo o rol administrador existente; no se sobrescribiran.');
        }

        // Ninguna contrasena se solicita ni cambia al repetir una inicializacion.
        $contrasena = $porNombre ? null : $this->pedirContrasena();

        $this->call(PermisoSeeder::class);

        foreach (self::ROLES as $nombreRol => $configuracion) {
            if ($existentes->contains('nombre', $nombreRol)) {
                continue;
            }

            $rol = Rol::create([
                'nombre' => $nombreRol,
                'descripcion' => $configuracion['descripcion'],
                'activo' => true,
            ]);
            $ids = $this->idsInstitucionales($configuracion['permisos']);
            $caja = Permiso::where('modulo', 'caja_chica')
                ->whereIn('accion', self::CAJA_CHICA[$nombreRol] ?? [])->pluck('id')->all();
            $rol->permisos()->syncWithoutDetaching(array_unique([...$ids, ...$caja]));
        }

        $rolAdmin = Rol::where('nombre', Rol::ADMINISTRADOR)->firstOrFail();
        $idsNecesarios = Permiso::pluck('id');
        $asignados = $rolAdmin->permisos()->pluck('permisos.id');

        if ($idsNecesarios->diff($asignados)->isNotEmpty()) {
            throw new RuntimeException('El rol administrador existente carece de permisos; se conserva y se revierte la inicializacion.');
        }

        $administrador = $porNombre ?? Usuario::create([
            'nombre_usuario' => $nombre,
            'correo_electronico' => $correo,
            'contrasena' => $contrasena,
            'rol_id' => $rolAdmin->id,
            'policia_id' => null,
            'activo' => true,
        ]);

        foreach (['ver', 'crear', 'descargar'] as $accion) {
            if (! $administrador->tienePermiso('caja_chica.'.$accion)) {
                throw new RuntimeException('El administrador no tiene los permisos efectivos de caja chica.');
            }
        }
    }

    private function pedirContrasena(): string
    {
        // Sin fallback visible: una terminal sin entrada oculta debe fallar.
        $contrasena = $this->command->secret('Contrasena (minimo 16 caracteres, mayusculas, minusculas, numeros y simbolos)', false);
        $confirmacion = $this->command->secret('Confirmar contrasena', false);
        $validacion = Validator::make([
            'contrasena' => $contrasena,
            'contrasena_confirmation' => $confirmacion,
        ], [
            'contrasena' => ['required', 'string', 'confirmed', Password::min(16)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validacion->fails()) {
            throw new RuntimeException('La contrasena no cumple los requisitos o la confirmacion no coincide.');
        }

        return $contrasena;
    }

    private function idsInstitucionales(array|string $matriz): array
    {
        if ($matriz === '*') {
            return Permiso::pluck('id')->all();
        }

        return Permiso::where(function ($query) use ($matriz) {
            foreach ($matriz as $modulo => $acciones) {
                $query->orWhere(function ($query) use ($modulo, $acciones) {
                    $query->where('modulo', $modulo);
                    if ($acciones !== '*') {
                        $query->whereIn('accion', $acciones);
                    }
                });
            }
        })->pluck('id')->all();
    }
}
