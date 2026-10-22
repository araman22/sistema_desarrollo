# Matriz de roles y permisos

Fuente: `database/seeders/RolSeeder.php` y `CajaChicaPermisoSeeder.php`.
Cada permiso se llama `modulo.accion` (ej. `oficinas.editar`).
El rol `administrador` tiene **todos** los permisos.

## Roles

| Rol | Descripción |
|---|---|
| administrador | Acceso total a todas las funcionalidades del sistema. |
| encargado_oficina | Gestiona policías, oficinas, proyectos y aprobaciones. |
| soporte | Administra equipamiento, inventario y mantenimientos tecnológicos. |
| administrativo | Registra movimientos económicos, licencias, vehículos y documentos. |
| invitado | Solo lectura sobre los módulos del sistema. |

## Matriz

| Módulo | encargado_oficina | soporte | administrativo | invitado |
|---|---|---|---|---|
| usuarios | — | — | — | — |
| roles | — | — | — | — |
| oficinas | ver, editar | — | — | ver |
| policias | ver, crear, editar, exportar | — | ver | ver |
| licencias | todas | — | ver, crear, editar | — |
| capacitaciones | todas | — | — | ver |
| asignaciones | — | — | — | — |
| actividades | todas | — | — | ver |
| inventarios | — | todas | — | ver |
| equipamientos | — | todas | — | — |
| vehiculos | — | — | ver, crear, editar | — |
| vehiculo_mantenimientos | — | — | ver, crear, editar | — |
| drones_robots | — | todas | — | — |
| baterias | — | todas | — | — |
| proyectos | todas | — | — | ver |
| incidencias | ver, crear, editar, resolver | ver, crear, editar, resolver | — | — |
| movimientos_economicos | ver, exportar | — | ver, crear, editar, exportar | — |
| caja_chica | ver, descargar | — | todas | — |
| documentos | — | — | ver, crear, editar, descargar | — |
| mantenimientos | — | todas | — | — |
| auditorias | ver | — | — | — |
| alertas | ver, atender | — | — | — |

"todas" = todas las acciones del módulo (ver `PermisoSeeder.php`).
"—" = sin acceso.

## Observaciones
- Solo `administrador` gestiona usuarios y roles.
- Nadie fuera de `administrador` puede **eliminar** en oficinas, policías, vehículos ni documentos.
- `tipo_documentos` usa los permisos de `documentos.*` (ver `TipoDocumentoPolicy`). `tipo_vehiculos` no tiene permisos propios en `PermisoSeeder`.