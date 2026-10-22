# Rutas protegidas y reglas de autorización

Fuente: `php artisan route:list -v` sobre la rama `testing`.
Los permisos de cada rol están en `matriz-roles-permisos.md`.

## Capas de protección

1. **Autenticación**: middleware `auth` (o `AccesoMovilidad` en movilidad). Sin sesión, redirige a `/login`.
2. **Usuario activo**: middleware `AsegurarUsuarioActivo` (global en `bootstrap/app.php`). Un usuario desactivado no puede operar.
3. **Autorización**: cada acción consulta la Policy del modelo, que verifica el permiso `modulo.accion`. Si no lo tiene, responde **403**.

## Rutas públicas (sin login)

| Ruta | Para qué | Protección extra |
|---|---|---|
| `GET /login`, `POST /login` | Iniciar sesión | `guest`; bloqueo tras 5 intentos fallidos |
| `/olvide-contrasena` | Pedir enlace de recuperación | `guest`, `throttle:6,1` |
| `/restablecer-contrasena` | Cambiar contraseña con token | `guest`, `throttle:6,1` |
| `/movilidad/acceso` | Login alternativo de movilidad | `guest`, `throttle:6,1` |

## Rutas protegidas

### A. Permiso verificado en la ruta (`routes/web.php`)

| Módulo | Ruta base | Ver | Crear | Editar | Eliminar | Otro |
|---|---|---|---|---|---|---|
| Oficinas | `/oficinas` | `oficinas.ver` | `oficinas.crear` | `oficinas.editar` | `oficinas.eliminar` | — |
| Inventarios | `/inventarios` | `inventarios.ver` | `inventarios.crear` | `inventarios.editar` | `inventarios.eliminar` | — |
| Equipamiento | `/equipamientos-tecnologicos` | `equipamientos.ver` | `equipamientos.crear` | `equipamientos.editar` | `equipamientos.eliminar` | — |
| Tipos de documento | `/tipo-documentos` | `documentos.ver` | `documentos.crear` | `documentos.editar` | `documentos.eliminar` | — |
| Documentos | `/documentos` | `documentos.ver` | `documentos.crear` | `documentos.editar` | `documentos.eliminar` | Descargar: `documentos.descargar` |
| Fondo fijo | `/fondo-fijo` | `fondo_fijo.ver` | `fondo_fijo.crear` | — | — | Ticket: `fondo_fijo.descargar` |

### B. Permiso verificado en el controlador

| Módulo | Ruta base | Controlador | Permisos |
|---|---|---|---|
| Usuarios | `/usuarios` | `UsuarioController` | `usuarios.*` (incluye activar/desactivar) |
| Roles | `/roles` | `RolController` | `roles.*` (incluye activar/desactivar) |
| Vehículos | `/vehiculos` | `VehiculoController` + `VehiculoRequest` | `vehiculos.*` |
| Mantenimiento de vehículos | `/vehiculos/{vehiculo}/mantenimientos` | `VehiculoMantenimientoController` | `vehiculo_mantenimientos.*` |
| Documentos de vehículos | `/vehiculos/{vehiculo}/documentos` | `VehiculoDocumentoController` | según `VehiculoPolicy` |
| Tipos de vehículo | `/tipos-vehiculos` | `TipoVehiculoController` | según `TipoVehiculoPolicy` |

### C. Movilidad: drones, robots y baterías

| Módulo | Ruta base | Protección |
|---|---|---|
| Drones y robots | `/movilidad/drones-robots` | `AccesoMovilidad` + `RecursoController` / `DroneRobotRequest` → `drones_robots.*` |
| Baterías | `/movilidad/baterias` | `AccesoMovilidad` + `RecursoController` / `BateriaRequest` → `baterias.*` |
| Mantenimientos | `/movilidad/{recurso}/mantenimientos` | `AccesoMovilidad` + `MantenimientoController` / `MantenimientoRequest` → `mantenimientos.*` |

## Respuestas ante acceso denegado

| Situación | Respuesta |
|---|---|
| Sin sesión | Redirige a `/login` |
| Usuario o rol desactivado | Cierra la sesión / 403 |
| Sin permiso para la acción | **403** con la vista `errors/403.blade.php` (no muestra datos) |
| Demasiados intentos | **429** |

## Regla para todos los equipos

Ocultar un botón con `@can` en la vista **no protege** la operación.
Toda ruta nueva debe tener `auth` y verificar su permiso en la ruta (`can:`), en el controlador (`authorize`) o en el FormRequest (`authorize()`).