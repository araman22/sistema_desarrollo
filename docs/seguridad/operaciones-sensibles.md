# Operaciones sensibles

Operaciones que deben verificar permiso en el servidor (no alcanza con ocultar el botón).

| Operación | Permiso requerido | Riesgo | Quién la tiene |
|---|---|---|---|
| Crear / editar / eliminar usuarios | `usuarios.crear` / `.editar` / `.eliminar` | Alta de cuentas o bloqueo de personas | administrador |
| Restablecer contraseña de otro usuario | `usuarios.restablecer` | Tomar control de una cuenta | administrador |
| Activar / desactivar usuarios | `usuarios.editar` | Dejar a alguien sin acceso | administrador |
| Crear / editar / eliminar roles | `roles.crear` / `.editar` / `.eliminar` | Cambiar qué puede hacer cada uno | administrador |
| Asignar permisos a un rol | `roles.asignar` | Escalada de privilegios | administrador |
| Eliminar cualquier registro | `*.eliminar` | Pérdida de datos | administrador, soporte (equipamientos, inventarios, mantenimientos, drones, baterías), encargado_oficina (licencias, capacitaciones, actividades, proyectos) |
| Autorizar licencias | `licencias.autorizar` | Aprobar ausencias sin control | administrador, encargado_oficina |
| Registrar movimientos económicos | `movimientos_economicos.crear` / `.editar` | Fraude o errores contables | administrador, administrativo |
| Movimientos de caja chica | `caja_chica.crear` | Manejo de dinero | administrador, administrativo |
| Descargar documentos y tickets | `documentos.descargar` / `caja_chica.descargar` | Fuga de información | administrador, administrativo, encargado_oficina (caja chica) |
| Exportar datos | `policias.exportar` / `movimientos_economicos.exportar` / `auditorias.exportar` | Fuga masiva de datos personales | ver matriz |
| Ver auditoría | `auditorias.ver` | Expone quién hizo qué | administrador, encargado_oficina |

## Estado actual (hallazgos)
- ⚠️ `oficinas`, `inventarios`, `equipamientos-tecnologicos`, `tipo-documentos` y `documentos` **no exigen login** (`routes/web.php`): hoy cualquiera puede eliminar o descargar.
- ⚠️ El módulo de vehículos (`routes/movilidad.php`) no está cargado en `bootstrap/app.php`.
- ✅ Usuarios, roles y caja chica sí verifican permisos.