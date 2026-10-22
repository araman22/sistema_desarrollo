# SIGDET: Etapa 1, integracion y acceso

> Nota: el módulo caja chica fue renombrado a fondo fijo (equipo backend, 21/10/2026).

Rama: `codex/grupo4-etapa1-acceso`, basada en `feature/grupo4-integrante-b`.

## Comportamiento

- `/` redirige a `/dashboard`; el dashboard autenticado ejecuta `DashboardController@index`.
- `Usuario` autentica contra `contrasena`, conserva `recordar_token` y dirige las notificaciones al `correo_electronico`.
- Fondo fijo exige `fondo_fijo.ver`, `fondo_fijo.crear` y `fondo_fijo.descargar` segun la operacion. Las rutas, el controlador y el FormRequest aplican autorizacion.
- El dashboard solo consulta y muestra los importes de fondo fijo si el usuario tiene permiso de lectura.
- Los comprobantes se descargan desde el disco privado configurado, conservando su extension; archivos inexistentes devuelven 404 despues de autorizar.
- Las nuevas policies de movimientos economicos y auditorias reutilizan los permisos existentes. Auditoria no admite creacion, edicion ni eliminacion por interfaz; el registro interno sigue funcionando.
- La policy de alertas exige permiso y coincidencia del destinatario para consultar o modificar un registro. `viewAny` habilita el listado; el futuro controlador debera filtrar la consulta por destinatario. No se agregaron endpoints de estos modulos.
- No se modificaron tablas, reglas contables, ni el trait de auditoria.

## Archivos compartidos con Grupo 1

Revisar en conjunto: `app/Models/Usuario.php`, `routes/web.php`, `database/seeders/PermisoSeeder.php`, `database/seeders/DatabaseSeeder.php` y `resources/views/layouts/app.blade.php`.

No se modifico `RolSeeder.php`. Para instalaciones existentes, el seeder independiente `FondoFijoPermisoSeeder` inserta permisos faltantes y agrega asignaciones con `syncWithoutDetaching`. No reemplaza descripciones, estados ni permisos personalizados.

Asignaciones aditivas a roles existentes:

| Rol | Permisos de fondo fijo |
| --- | --- |
| administrador | ver, crear, descargar |
| administrativo | ver, crear, descargar |
| encargado_oficina | ver, descargar |

No crea roles ni modifica roles con otros nombres. No reactiva roles inactivos. El seeder no fue aplicado a la base local; se ejecuto solo en SQLite en memoria durante las pruebas. Para activar en una instalacion existente se usa exclusivamente:

```sh
php artisan db:seed --class=FondoFijoPermisoSeeder
```

No ejecutar `DatabaseSeeder` como actualizacion de permisos: incluye el `RolSeeder` preexistente que sincroniza asignaciones y modifica roles base.

## Verificacion

PHPUnit se ejecuto con `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `DB_URL` vacio, sesiones y cache en memoria, y `--do-not-cache-result`. No se ejecutaron migraciones ni seeders sobre MySQL.

La suite nueva `Grupo4AccesoTest` cubre login real, recordar usuario, clave incorrecta, limite de intentos, usuario inactivo, recuperacion, token invalido, logout, invitados, separacion de permisos, descarga privada, roles inactivos, policies y conservacion de personalizaciones.

Resultado de la suite completa: 23 pruebas, 118 aserciones, 21 aprobadas y 2 fallidas. Los fallos corresponden a endpoints preexistentes fuera del alcance de fondo fijo:

- `ModuloSigdetGrupo2Test::test_usuario_sin_permiso_no_puede_acceder_al_modulo`: oficinas responde 200 cuando deberia responder 403.
- `ModuloSigdetGrupo2Test::test_descarga_de_documento_no_autorizada_es_rechazada`: documentos responde 200 cuando deberia responder 403.

Se preservaron esas pruebas. No se modificaron los controladores de oficinas o documentos. Falta validar contra MySQL aislado y entrega real de correo SMTP; las pruebas de recuperacion usan notificaciones simuladas.

No se creo un PR. La Etapa 2 requiere autorizacion independiente.

## Revision final

- Al realizar la revision, HEAD y `feature/grupo4-integrante-b` apuntaban a `ad2e2bfd6ba255166e0d356832f225a166da931a` y los cambios aun no tenian commit. La comparacion incluyo el directorio de trabajo y los archivos nuevos: 12 modificados y 7 nuevos, 19 en total.
- Se exporto la rama base con `git archive` a una carpeta temporal, sin cambiar de rama ni copiar `.env`. La base exacta no arranca por un import duplicado de `Route`. Solo en esa copia se elimino el segundo import y se proporciono una clave de pruebas. Con SQLite en memoria, las dos pruebas de oficinas/documentos reprodujeron los mismos fallos 200/403. No son regresiones de la Etapa 1.
- La comparacion de rutas efectivas dio 67 rutas en ambas versiones. Solo cambian los tres endpoints de fondo fijo (middleware de acceso) y el dashboard (controlador). No se perdieron rutas ni quedaron nombres duplicados.
- Se repitio la suite completa: 23 pruebas, 118 aserciones; 21 aprobadas y los mismos 2 fallos preexistentes.
- Dos pruebas adicionales temporales aprobaron 19 aserciones: unicidad y accion del dashboard, redirecciones, asignaciones aditivas de los tres roles base, preservacion de un rol y permiso personalizados, estados/descripciones intactos y snapshot identico de `roles`, `permisos` y `rol_permiso` al repetir el seeder.
- `FondoFijoPermisoSeeder` y `PermisoSeeder` son aditivos e idempotentes. `DatabaseSeeder` y `RolSeeder` NO son seguros como actualizacion de una instalacion personalizada: conservan el comportamiento previo de sincronizar permisos de roles base. Usar exclusivamente el seeder especifico.
- El seeder especifico llama al catalogo completo de `PermisoSeeder`: agrega tambien otros permisos estandar faltantes, sin modificar los existentes ni asignarlos a roles.

La Etapa 1 esta lista para validacion local de su alcance. No equivale a declarar que todo SIGDET tiene autorizacion completa: siguen pendientes oficinas/documentos, validacion MySQL y SMTP reales, y el trabajo contable/auditoria de etapas posteriores. Los roles personalizados no reciben automaticamente permisos de fondo fijo; se asignan de manera explicita desde la gestion existente de roles.

La revision fue aprobada para validacion local. Los 19 archivos se entregan mediante el commit `feat(grupo4): implementar autenticacion y permisos de etapa 1` en `codex/grupo4-etapa1-acceso`, sin cambios funcionales adicionales. No se autoriza comenzar la Etapa 2 ni crear un Pull Request.

## Complemento: inicializacion local segura

Se agrega `database/seeders/InicializacionLocalSeeder.php`, de ejecucion explicita. No se incorpora a `DatabaseSeeder` ni se modifica ningun archivo del Grupo 1.

El inicializador exige `APP_ENV=local` y la conexion efectiva configurada como MySQL, host exactamente `127.0.0.1`, base `desarrollo_sistema`, sin socket, prefijo de tablas ni configuracion alternativa de lectura/escritura. No admite `--no-interaction`, no recibe contrasenas por argumentos o variables de entorno y no contiene credenciales predeterminadas. `--force` no elude estas comprobaciones.

Antes de crear registros solicita nombre y correo del administrador. Solo si ese usuario no existe solicita una contrasena oculta y su confirmacion, sin fallback visible. Requiere al menos 16 caracteres, mayusculas, minusculas, numeros y simbolos. Si el driver es bcrypt se aplica un maximo de 72 bytes mediante `strlen`, no de caracteres: Unicode puede ocupar varios bytes por caracter. Otros drivers, como Argon2id, no reciben ese limite de bcrypt. `Usuario` la almacena mediante su cast `hashed`. Al repetir, no solicita ni modifica la contrasena existente.

La operacion usa una transaccion y un bloqueo MySQL `GET_LOCK`, liberado incluso ante errores, para serializar ejecuciones del inicializador. Crea solamente los roles institucionales ausentes (`administrador`, `encargado_oficina`, `soporte`, `administrativo`, `invitado`) y el usuario solicitado ausente. No crea personas, policias, oficinas, armas ni usuarios de prueba; el administrador usa `policia_id=NULL`.

Para evitar modificar o ejecutar seeders del Grupo 1 se mantienen copias locales de la matriz de roles y del catalogo institucional, con pruebas de paridad contra `RolSeeder` y `PermisoSeeder`. No se invoca ningun otro seeder. Se insertan solamente permisos institucionales ausentes; se preservan permisos personalizados y descripciones existentes. Antes de escribir se rechaza cualquier permiso institucional cuyo nombre, modulo o accion no coincida con el catalogo, incluidas variantes ambiguas de mayusculas/espacios. A los roles recien creados se asignan los permisos institucionales; el administrador recibe todos los permisos del catalogo, incluidos personalizados, siguiendo el criterio del Grupo 1. Fondo fijo se asigna asi:

| Rol nuevo | Permisos de fondo fijo |
| --- | --- |
| administrador | ver, crear, descargar |
| administrativo | ver, crear, descargar |
| encargado_oficina | ver, descargar |
| soporte e invitado | ninguno |

Roles, usuarios y asignaciones preexistentes se conservan. No se intenta completar silenciosamente los permisos de un rol existente. Si el administrador existente esta inactivo, tiene otra identidad/rol o carece de permisos, se aborta. Tambien se aborta ante correo ocupado por otro usuario, nombre institucional duplicado o credenciales invalidas. Los registros nuevos de permisos, roles, pivote, usuario y auditoria se revierten ante errores transaccionales.

### Comprobaciones obligatorias antes de escribir

Con el bloqueo adquirido, el inicializador inspecciona la base efectiva (`DATABASE()`) y exige `foreign_key_checks=1`. Consulta metadatos mediante el schema builder de Laravel; no intenta reparar el esquema ni ejecutar DDL.

- Exige tablas InnoDB: `roles`, `permisos`, `usuarios`, `rol_permiso`, `auditorias` y `policias` (destino de la FK opcional del usuario).
- Comprueba columnas utilizadas, clave primaria `id` y autoincremento. `usuarios.policia_id` y `auditorias.usuario_id` deben admitir NULL.
- Exige unicidad de `permisos.nombre`, `usuarios.nombre_usuario`, `usuarios.correo_electronico` y el par `rol_permiso(rol_id, permiso_id)`.
- Comprueba las FK de usuario a rol/policia, pivote a rol/permiso y auditoria a usuario; todas deben referenciar `id` en la misma base. Exige las reglas de borrado del proyecto y actualizacion RESTRICT/NO ACTION.

El usuario de base de datos debe poder leer esos metadatos y ejecutar `GET_LOCK`/`RELEASE_LOCK`, ademas de las operaciones necesarias. Si falta una tabla, motor o restriccion, se aborta sin corregir ni escribir registros. Esta comprobacion no equivale a certificar todos los tipos/longitudes, triggers o datos historicos del sistema: cualquier error de persistencia se revierte transaccionalmente.

`roles.nombre` no tiene UNIQUE en las migraciones del Grupo 1. No se agrega esa restriccion ni se cambia su esquema: se detectan duplicados/variantes y se serializan ejecuciones del inicializador. Evitar cambios administrativos o ejecuciones de otros seeders en paralelo, pues no participan de este bloqueo.

### Auditoria obligatoria

Las altas se realizan sin disparar el trait tolerante, exclusivamente dentro de este inicializador, y se registran explicitamente mediante el modelo existente `Auditoria`. Se audita cada permiso, rol y usuario creado, las asignaciones por rol y un resumen de inicializacion. El pivote utiliza `registro_id=rol_id`, siguiendo la convencion del controlador de roles.

Cada fila incluye `origen=CLI`, proceso, un UUID de lote y datos seleccionados; `usuario_id` y `direccion_ip` son NULL porque no hay actor web autenticado. No se incluyen contrasenas, hashes ni volcados completos de usuarios. No se modifica el trait ni ningun archivo del Grupo 1. Si guardar cualquier auditoria devuelve false o lanza una excepcion, se revierte toda la inicializacion, incluidas las auditorias anteriores del lote. Una repeticion sin cambios no agrega nuevas auditorias: se mantiene la idempotencia de registros.

En la instalacion con roles y usuarios vacios, el resultado esperado es cinco roles institucionales y un administrador activo, con los tres permisos efectivos de fondo fijo. La segunda ejecucion con el mismo nombre y correo conserva los datos, incluidos hash y timestamps.

### Ejecucion manual en PowerShell

Estos comandos son para ejecutarlos en tu computadora. No fueron ejecutados sobre MySQL durante el desarrollo:

```powershell
Set-Location 'C:\Proyectos\sistema_desarrollo'
php artisan config:clear
if ($LASTEXITCODE -ne 0) { throw 'No se pudo limpiar la configuracion.' }
php artisan db:seed --class=InicializacionLocalSeeder
if ($LASTEXITCODE -ne 0) { throw 'Inicializacion detenida; revisar el diagnostico.' }
```

Verificar previamente en `.env` los valores `APP_ENV=local`, `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1` y `DB_DATABASE=desarrollo_sistema`. No cambiar `APP_KEY`. No usar `migrate:fresh`, `DatabaseSeeder`, `RolSeeder`, `UsuarioAdminSeeder` ni `UsuariosPruebaSeeder` para esta inicializacion. No hace falta ejecutar migraciones. El bloqueo solo coordina ejecuciones de este inicializador; evitar ediciones administrativas simultaneas durante la inicializacion.

### Pruebas del complemento

`tests/Feature/InicializacionLocalSeederTest.php` usa SQLite en memoria mediante un adaptador exclusivo de tests. La clase real no tiene excepciones de seguridad para `testing` o SQLite. Las guardias reales y el bloqueo MySQL se prueban por separado con conexiones simuladas, sin abrir conexiones MySQL.

Se verifica creacion, login real, permisos institucionales y de fondo fijo, ausencia de fichas ficticias, contrasenas hasheadas y excluidas de auditoria, repeticion sin cambios, personalizaciones preservadas, conflictos de nombre/correo, roles duplicados/inactivos, contrasenas invalidas, modo no interactivo y rollback tras fallos de persistencia. La validacion real del servidor MySQL y de la entrada oculta en la terminal local queda a cargo de la ejecucion manual.

Resultados anteriores a la correccion de compatibilidad: 25 pruebas aprobadas, 132 aserciones. Suite completa de esa version: 48 pruebas, 250 aserciones; 46 aprobadas y los mismos 2 fallos preexistentes de oficinas/documentos.

La suite ampliada verifica el catalogo inconsistente, limites ASCII/Unicode de bcrypt, Argon2id, metadatos MySQL simulados, ausencia de restricciones, claves desactivadas, auditorias CLI y rollback si falla la auditoria de altas, asignaciones o resumen (tambien si un evento cancela silenciosamente su guardado). Se conservan login, idempotencia, preservacion de personalizaciones y paridad con el Grupo 1. Los metadatos y el bloqueo MySQL se simulan; no se ejecutaron operaciones sobre MySQL local. Los dos fallos de autorizacion preexistentes de oficinas/documentos siguen fuera del alcance de este complemento.

Resultados de la version corregida: 58 pruebas del inicializador aprobadas, 861 aserciones. Suite completa: 81 pruebas, 979 aserciones; 79 aprobadas y los mismos 2 fallos preexistentes. Laravel Pint y `git diff --check` correctos. El inicializador esta listo para la ejecucion manual local condicionada a superar su preflight; si el esquema instalado no cumple, se detendra sin repararlo. No se certifico ni altero el MySQL del usuario.

Entrega en un commit independiente de la Etapa 1, con solo este documento, el seeder nuevo y su suite. Sin ejecuciones sobre MySQL, migraciones, cambios en los archivos del Grupo 1, Pull Request ni avance a la Etapa 2.
