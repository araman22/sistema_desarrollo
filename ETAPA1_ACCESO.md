# SIGDET: Etapa 1, integracion y acceso

Rama: `codex/grupo4-etapa1-acceso`, basada en `feature/grupo4-integrante-b`.

## Comportamiento

- `/` redirige a `/dashboard`; el dashboard autenticado ejecuta `DashboardController@index`.
- `Usuario` autentica contra `contrasena`, conserva `recordar_token` y dirige las notificaciones al `correo_electronico`.
- Caja chica exige `caja_chica.ver`, `caja_chica.crear` y `caja_chica.descargar` segun la operacion. Las rutas, el controlador y el FormRequest aplican autorizacion.
- El dashboard solo consulta y muestra los importes de caja chica si el usuario tiene permiso de lectura.
- Los comprobantes se descargan desde el disco privado configurado, conservando su extension; archivos inexistentes devuelven 404 despues de autorizar.
- Las nuevas policies de movimientos economicos y auditorias reutilizan los permisos existentes. Auditoria no admite creacion, edicion ni eliminacion por interfaz; el registro interno sigue funcionando.
- La policy de alertas exige permiso y coincidencia del destinatario para consultar o modificar un registro. `viewAny` habilita el listado; el futuro controlador debera filtrar la consulta por destinatario. No se agregaron endpoints de estos modulos.
- No se modificaron tablas, reglas contables, ni el trait de auditoria.

## Archivos compartidos con Grupo 1

Revisar en conjunto: `app/Models/Usuario.php`, `routes/web.php`, `database/seeders/PermisoSeeder.php`, `database/seeders/DatabaseSeeder.php` y `resources/views/layouts/app.blade.php`.

No se modifico `RolSeeder.php`. Para instalaciones existentes, el seeder independiente `CajaChicaPermisoSeeder` inserta permisos faltantes y agrega asignaciones con `syncWithoutDetaching`. No reemplaza descripciones, estados ni permisos personalizados.

Asignaciones aditivas a roles existentes:

| Rol | Permisos de caja chica |
| --- | --- |
| administrador | ver, crear, descargar |
| administrativo | ver, crear, descargar |
| encargado_oficina | ver, descargar |

No crea roles ni modifica roles con otros nombres. No reactiva roles inactivos. El seeder no fue aplicado a la base local; se ejecuto solo en SQLite en memoria durante las pruebas. Para activar en una instalacion existente se usa exclusivamente:

```sh
php artisan db:seed --class=CajaChicaPermisoSeeder
```

No ejecutar `DatabaseSeeder` como actualizacion de permisos: incluye el `RolSeeder` preexistente que sincroniza asignaciones y modifica roles base.

## Verificacion

PHPUnit se ejecuto con `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `DB_URL` vacio, sesiones y cache en memoria, y `--do-not-cache-result`. No se ejecutaron migraciones ni seeders sobre MySQL.

La suite nueva `Grupo4AccesoTest` cubre login real, recordar usuario, clave incorrecta, limite de intentos, usuario inactivo, recuperacion, token invalido, logout, invitados, separacion de permisos, descarga privada, roles inactivos, policies y conservacion de personalizaciones.

Resultado de la suite completa: 23 pruebas, 118 aserciones, 21 aprobadas y 2 fallidas. Los fallos corresponden a endpoints preexistentes fuera del alcance de caja chica:

- `ModuloSigdetGrupo2Test::test_usuario_sin_permiso_no_puede_acceder_al_modulo`: oficinas responde 200 cuando deberia responder 403.
- `ModuloSigdetGrupo2Test::test_descarga_de_documento_no_autorizada_es_rechazada`: documentos responde 200 cuando deberia responder 403.

Se preservaron esas pruebas. No se modificaron los controladores de oficinas o documentos. Falta validar contra MySQL aislado y entrega real de correo SMTP; las pruebas de recuperacion usan notificaciones simuladas.

No se creo un PR. La Etapa 2 requiere autorizacion independiente.

## Revision final

- Al realizar la revision, HEAD y `feature/grupo4-integrante-b` apuntaban a `ad2e2bfd6ba255166e0d356832f225a166da931a` y los cambios aun no tenian commit. La comparacion incluyo el directorio de trabajo y los archivos nuevos: 12 modificados y 7 nuevos, 19 en total.
- Se exporto la rama base con `git archive` a una carpeta temporal, sin cambiar de rama ni copiar `.env`. La base exacta no arranca por un import duplicado de `Route`. Solo en esa copia se elimino el segundo import y se proporciono una clave de pruebas. Con SQLite en memoria, las dos pruebas de oficinas/documentos reprodujeron los mismos fallos 200/403. No son regresiones de la Etapa 1.
- La comparacion de rutas efectivas dio 67 rutas en ambas versiones. Solo cambian los tres endpoints de caja chica (middleware de acceso) y el dashboard (controlador). No se perdieron rutas ni quedaron nombres duplicados.
- Se repitio la suite completa: 23 pruebas, 118 aserciones; 21 aprobadas y los mismos 2 fallos preexistentes.
- Dos pruebas adicionales temporales aprobaron 19 aserciones: unicidad y accion del dashboard, redirecciones, asignaciones aditivas de los tres roles base, preservacion de un rol y permiso personalizados, estados/descripciones intactos y snapshot identico de `roles`, `permisos` y `rol_permiso` al repetir el seeder.
- `CajaChicaPermisoSeeder` y `PermisoSeeder` son aditivos e idempotentes. `DatabaseSeeder` y `RolSeeder` NO son seguros como actualizacion de una instalacion personalizada: conservan el comportamiento previo de sincronizar permisos de roles base. Usar exclusivamente el seeder especifico.
- El seeder especifico llama al catalogo completo de `PermisoSeeder`: agrega tambien otros permisos estandar faltantes, sin modificar los existentes ni asignarlos a roles.

La Etapa 1 esta lista para validacion local de su alcance. No equivale a declarar que todo SIGDET tiene autorizacion completa: siguen pendientes oficinas/documentos, validacion MySQL y SMTP reales, y el trabajo contable/auditoria de etapas posteriores. Los roles personalizados no reciben automaticamente permisos de caja chica; se asignan de manera explicita desde la gestion existente de roles.

La revision fue aprobada para validacion local. Los 19 archivos se entregan mediante el commit `feat(grupo4): implementar autenticacion y permisos de etapa 1` en `codex/grupo4-etapa1-acceso`, sin cambios funcionales adicionales. No se autoriza comenzar la Etapa 2 ni crear un Pull Request.
