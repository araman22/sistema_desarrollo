<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

    private const PERMISOS = [
        'usuarios' => ['ver', 'crear', 'editar', 'eliminar', 'restablecer'],
        'roles' => ['ver', 'crear', 'editar', 'eliminar', 'asignar'],
        'oficinas' => ['ver', 'crear', 'editar', 'eliminar'],
        'policias' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
        'licencias' => ['ver', 'crear', 'editar', 'eliminar', 'autorizar'],
        'capacitaciones' => ['ver', 'crear', 'editar', 'eliminar', 'inscribir'],
        'asignaciones' => ['ver', 'crear', 'editar', 'eliminar'],
        'actividades' => ['ver', 'crear', 'editar', 'eliminar'],
        'inventarios' => ['ver', 'crear', 'editar', 'eliminar'],
        'equipamientos' => ['ver', 'crear', 'editar', 'eliminar'],
        'vehiculos' => ['ver', 'crear', 'editar', 'eliminar'],
        'vehiculo_mantenimientos' => ['ver', 'crear', 'editar', 'eliminar'],
        'drones_robots' => ['ver', 'crear', 'editar', 'eliminar'],
        'baterias' => ['ver', 'crear', 'editar', 'eliminar'],
        'proyectos' => ['ver', 'crear', 'editar', 'eliminar'],
        'incidencias' => ['ver', 'crear', 'editar', 'eliminar', 'resolver'],
        'movimientos_economicos' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'],
        'caja_chica' => ['ver', 'crear', 'descargar'],
        'documentos' => ['ver', 'crear', 'editar', 'eliminar', 'descargar'],
        'mantenimientos' => ['ver', 'crear', 'editar', 'eliminar'],
        'auditorias' => ['ver', 'exportar'],
        'alertas' => ['ver', 'crear', 'editar', 'eliminar', 'atender'],
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
            $this->validarEsquema($conexion);
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
            || filled($conexion->getConfig('prefix'))
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

    protected function validarEsquema(Connection $conexion): void
    {
        $estado = $conexion->selectOne('SELECT DATABASE() AS base_actual, @@SESSION.foreign_key_checks AS claves_foraneas');
        if ($estado->base_actual !== 'desarrollo_sistema' || (int) $estado->claves_foraneas !== 1) {
            throw new RuntimeException('Esquema rechazado: base efectiva incorrecta o claves foraneas desactivadas.');
        }

        $esquema = $conexion->getSchemaBuilder();
        $tablas = collect($esquema->getTables('desarrollo_sistema'))->keyBy('name');
        $columnas = [
            'roles' => ['id', 'nombre', 'descripcion', 'activo', 'created_at', 'updated_at'],
            'permisos' => ['id', 'nombre', 'descripcion', 'modulo', 'accion', 'created_at', 'updated_at'],
            'usuarios' => ['id', 'rol_id', 'policia_id', 'nombre_usuario', 'correo_electronico', 'contrasena', 'activo', 'ultimo_acceso', 'recordar_token', 'created_at', 'updated_at'],
            'rol_permiso' => ['id', 'rol_id', 'permiso_id'],
            'auditorias' => ['id', 'usuario_id', 'accion', 'tabla_afectada', 'registro_id', 'valor_anterior', 'valor_nuevo', 'direccion_ip', 'created_at', 'updated_at'],
            'policias' => ['id'],
        ];
        $unicos = [
            'permisos' => [['nombre']],
            'usuarios' => [['nombre_usuario'], ['correo_electronico']],
            'rol_permiso' => [['rol_id', 'permiso_id']],
        ];

        foreach ($columnas as $tabla => $requeridas) {
            if (strtolower($tablas->get($tabla)['engine'] ?? '') !== 'innodb') {
                throw new RuntimeException('Esquema rechazado: tabla '.$tabla.' ausente o sin motor InnoDB.');
            }

            $definiciones = collect($esquema->getColumns($tabla))->keyBy('name');
            if (array_diff($requeridas, $definiciones->keys()->all()) !== []) {
                throw new RuntimeException('Esquema rechazado: faltan columnas en '.$tabla.'.');
            }
            if (! $definiciones['id']['auto_increment']) {
                throw new RuntimeException('Esquema rechazado: id sin autoincremento en '.$tabla.'.');
            }
            if ($tabla === 'usuarios' && ! $definiciones['policia_id']['nullable']) {
                throw new RuntimeException('Esquema rechazado: usuarios.policia_id debe admitir NULL.');
            }
            if ($tabla === 'auditorias' && ! $definiciones['usuario_id']['nullable']) {
                throw new RuntimeException('Esquema rechazado: auditorias.usuario_id debe admitir origen CLI sin actor autenticado.');
            }

            $indices = collect($esquema->getIndexes($tabla));
            if (! $indices->contains(fn ($indice) => $indice['primary'] && $indice['columns'] === ['id'])) {
                throw new RuntimeException('Esquema rechazado: falta clave primaria id en '.$tabla.'.');
            }
            foreach ($unicos[$tabla] ?? [] as $campos) {
                if (! $indices->contains(fn ($indice) => $indice['unique']
                    && count($indice['columns']) === count($campos)
                    && array_diff($campos, $indice['columns']) === [])) {
                    throw new RuntimeException('Esquema rechazado: falta unicidad de '.$tabla.'.'.implode(',', $campos).'.');
                }
            }
        }

        $relaciones = [
            'usuarios' => [['rol_id', 'roles', ['restrict', 'no action']], ['policia_id', 'policias', ['set null']]],
            'rol_permiso' => [['rol_id', 'roles', ['cascade']], ['permiso_id', 'permisos', ['cascade']]],
            'auditorias' => [['usuario_id', 'usuarios', ['set null']]],
        ];
        foreach ($relaciones as $tabla => $requeridas) {
            $claves = collect($esquema->getForeignKeys($tabla));
            foreach ($requeridas as [$columna, $destino, $borrado]) {
                if (! $claves->contains(fn ($clave) => $clave['columns'] === [$columna]
                    && $clave['foreign_schema'] === 'desarrollo_sistema'
                    && $clave['foreign_table'] === $destino
                    && $clave['foreign_columns'] === ['id']
                    && in_array(strtolower($clave['on_delete']), $borrado, true)
                    && in_array(strtolower($clave['on_update']), ['restrict', 'no action'], true))) {
                    throw new RuntimeException('Esquema rechazado: clave foranea incompatible en '.$tabla.'.'.$columna.'.');
                }
            }
        }
    }

    private function validarCatalogo(): void
    {
        foreach (Permiso::all() as $permiso) {
            // Se preservan permisos personalizados, pero no variantes ambiguas del catalogo.
            $nombre = mb_strtolower(trim($permiso->nombre));
            foreach (self::PERMISOS as $modulo => $acciones) {
                foreach ($acciones as $accion) {
                    if ($nombre === $modulo.'.'.$accion
                        && ($permiso->nombre !== $nombre || $permiso->modulo !== $modulo || $permiso->accion !== $accion)) {
                        throw new RuntimeException('Catalogo inconsistente: permiso institucional id '.$permiso->id.'. No se modificaron registros.');
                    }
                }
            }
        }
    }

    private function auditar(string $accion, string $tabla, int $registroId, array $datos, string $lote): void
    {
        $auditoria = new Auditoria([
            'usuario_id' => null,
            'accion' => $accion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'valor_anterior' => null,
            'valor_nuevo' => ['origen' => 'CLI', 'proceso' => self::class, 'lote' => $lote, 'datos' => $datos],
            'direccion_ip' => null,
        ]);
        // No usar el trait tolerante: la auditoria del inicializador es obligatoria.
        if (! $auditoria->save()) {
            throw new RuntimeException('No se pudo guardar la auditoria obligatoria de inicializacion.');
        }
    }

    private function inicializar(string $nombre, string $correo): void
    {
        $this->validarCatalogo();
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

        $lote = (string) Str::uuid();
        $cambios = ['permisos' => [], 'roles' => [], 'usuario' => null];
        foreach (self::PERMISOS as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $nombrePermiso = $modulo.'.'.$accion;
                if (Permiso::where('nombre', $nombrePermiso)->exists()) {
                    continue;
                }
                $permiso = Permiso::createQuietly([
                    'nombre' => $nombrePermiso, 'modulo' => $modulo, 'accion' => $accion,
                    'descripcion' => 'Permite '.$accion.' registros de '.$modulo.'.',
                ]);
                $cambios['permisos'][] = $permiso->id;
                $this->auditar('crear', 'permisos', $permiso->id, ['nombre' => $nombrePermiso, 'modulo' => $modulo, 'accion' => $accion], $lote);
            }
        }

        foreach (self::ROLES as $nombreRol => $configuracion) {
            if ($existentes->contains('nombre', $nombreRol)) {
                continue;
            }

            $rol = Rol::createQuietly([
                'nombre' => $nombreRol,
                'descripcion' => $configuracion['descripcion'],
                'activo' => true,
            ]);
            $ids = $this->idsInstitucionales($configuracion['permisos']);
            $caja = Permiso::where('modulo', 'caja_chica')
                ->whereIn('accion', self::CAJA_CHICA[$nombreRol] ?? [])->pluck('id')->all();
            $rol->permisos()->syncWithoutDetaching(array_unique([...$ids, ...$caja]));
            $cambios['roles'][] = $rol->id;
            $this->auditar('crear', 'roles', $rol->id, ['nombre' => $rol->nombre, 'activo' => true], $lote);
            $this->auditar('asignar_permisos', 'rol_permiso', $rol->id, [
                'rol_id' => $rol->id,
                'permisos' => $rol->permisos()->orderBy('nombre')->pluck('nombre')->all(),
            ], $lote);
        }

        $rolAdmin = Rol::where('nombre', Rol::ADMINISTRADOR)->firstOrFail();
        $idsNecesarios = Permiso::pluck('id');
        $asignados = $rolAdmin->permisos()->pluck('permisos.id');

        if ($idsNecesarios->diff($asignados)->isNotEmpty()) {
            throw new RuntimeException('El rol administrador existente carece de permisos; se conserva y se revierte la inicializacion.');
        }

        $administrador = $porNombre ?? Usuario::createQuietly([
            'nombre_usuario' => $nombre,
            'correo_electronico' => $correo,
            'contrasena' => $contrasena,
            'rol_id' => $rolAdmin->id,
            'policia_id' => null,
            'activo' => true,
        ]);
        if (! $porNombre) {
            $cambios['usuario'] = $administrador->id;
            $this->auditar('crear', 'usuarios', $administrador->id, [
                'nombre_usuario' => $nombre, 'rol_id' => $rolAdmin->id, 'activo' => true,
            ], $lote);
        }

        foreach (['ver', 'crear', 'descargar'] as $accion) {
            if (! $administrador->tienePermiso('caja_chica.'.$accion)) {
                throw new RuntimeException('El administrador no tiene los permisos efectivos de caja chica.');
            }
        }
        if ($cambios['permisos'] !== [] || $cambios['roles'] !== [] || $cambios['usuario'] !== null) {
            $this->auditar('inicializar_local', 'usuarios', $administrador->id, $cambios, $lote);
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

        if (Hash::getDefaultDriver() === 'bcrypt' && is_string($contrasena) && strlen($contrasena) > 72) {
            throw new RuntimeException('La contrasena bcrypt no puede superar 72 bytes (incluidos caracteres Unicode).');
        }

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
