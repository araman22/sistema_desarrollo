# Grupo 3 — Integrante B: Drones/robots y mantenimiento

Implementación en la rama existente `acuña`. No se crean otras ramas.

## Alcance

- CRUD de drones y robots: identidad, código, serie, estado, responsable, adquisición y horas acumuladas de uso/vuelo.
- CRUD de baterías: asociación con dron/robot, código, serie, capacidad con unidad, estado, ciclos, adquisición y última revisión.
- Revisiones y mantenimientos polimórficos de drones/robots y baterías: tipo, fecha, responsable, descripción, costo, estado y resultado.
- Búsquedas, filtros, paginación, vistas de listado/alta/edición/detalle y eliminación protegida.
- FormRequests, Policies/Gates, control de sesión y cuentas/roles activos, CSRF, escape de salida, protección de IDs y mass assignment.
- Factories, seeder optativo de demostración y pruebas funcionales.

No se implementa el CRUD de vehículos del integrante A, el login del Grupo 1 ni la administración de auditorías/alertas del Grupo 4.

## Instalación y datos

Con dependencias instaladas y una base MySQL configurada en `.env`:

```shell
php artisan migrate
php artisan serve
```

La única migración nueva agrega índices únicos para series no nulas de drones/robots y baterías. Los códigos ya eran únicos en las migraciones originales. Antes de aplicar los índices comprueba ambas tablas y falla con un mensaje si hay series repetidas; no borra ni altera esos datos. Deben revisarse las series repetidas con el equipo. No usar `migrate:fresh` sobre una base con datos.

Para datos de demostración, de forma optativa:

```shell
php artisan db:seed --class=MovilidadDemoSeeder
```

El seeder es idempotente, conserva los datos existentes, crea un dron, un robot, sus baterías y revisiones programadas. No crea usuarios ni concede permisos. No se agrega al DatabaseSeeder global para evitar cargar datos ficticios en todas las instalaciones.

Factories: DroneRobotFactory, BateriaFactory y MantenimientoFactory. `MantenimientoMovilidad::factory()` permite generar mantenimientos de prueba; para asociarlos a un padre determinado, usar su relación `mantenimientos()` o especificar ambos campos polimórficos coherentes en la factory.

## Rutas e integración con autenticación

Entrada: `/movilidad`. Listados: `/movilidad/drones-robots` y `/movilidad/baterias`.

Ejemplos de historial:

```text
/movilidad/drones-robots/{drone}/mantenimientos
/movilidad/baterias/{bateria}/mantenimientos
```

Las rutas usan el guard `web` y el modelo Usuario del proyecto. AccesoMovilidad verifica sesión y cuenta/rol activos. Un anónimo recibe 401; si el Grupo 1 registra una ruta nombrada `login`, las peticiones HTML redirigen automáticamente a ese login conservando la URL de destino. Las peticiones JSON siguen recibiendo 401.

En la versión de partida no existe un login funcional. El módulo no inventa un segundo sistema de autenticación. Para usar las pantallas con cuentas reales debe integrarse el acceso del Grupo 1, que también debe adaptar su autenticación a `usuarios.contrasena`. Las pruebas usan sesiones autenticadas de Laravel y no requieren ese login.

## Permisos

Se usa el catálogo existente, sin conceder accesos por nombre de rol ni crear una excepción de administrador.

| Acción | Permiso |
| --- | --- |
| Consultar drones/robots | `drones_robots.ver` |
| Crear, editar, eliminar drones/robots | `drones_robots.crear`, `.editar`, `.eliminar`, respectivamente |
| Consultar baterías | `baterias.ver` y `drones_robots.ver` |
| Crear, editar, eliminar baterías | Acción correspondiente de `baterias` y `drones_robots.ver` |
| Consultar historial y detalle | `mantenimientos.ver` y permiso de consulta del recurso padre |
| Registrar mantenimiento | `mantenimientos.crear` y permiso de consulta del recurso padre |
| Editar programación/trabajo en curso | `mantenimientos.editar` y permiso de consulta del recurso padre |
| Eliminar programación | `mantenimientos.eliminar` y permiso de consulta del recurso padre |

El catálogo actual define acceso por módulo, no por oficina o dueño. Por ello estos permisos autorizan registros de todo el módulo. Si el Grupo 1 incorpora alcance por oficina, debe extenderse en estas Policies y en las consultas. Un usuario con consulta no puede crear, editar ni eliminar por URL directa. El estado del mantenimiento restringe las acciones aun teniendo permisos.

## Datos y estados

- Tipos de recurso: `dron`, `robot`.
- Estados de recurso: `operativo`, `en_mantenimiento`, `fuera_servicio`, `baja`.
- Estados de batería: `disponible`, `en_uso`, `en_revision`, `agotada`, `baja`.
- Tipos de mantenimiento: `preventivo`, `correctivo`, `revision`.
- Estados de mantenimiento: `programado`, `en_curso`, `realizado`, `cancelado`.

Horas no negativas, hasta dos decimales y máximo DECIMAL(10,2). Ciclos enteros entre 0 y 4294967295. Ambos son acumulados y nunca disminuyen; se verifica de nuevo dentro de la transacción sobre la fila bloqueada. Las fechas de adquisición/revisión no son futuras. La última revisión de una batería no puede borrarse, retroceder ni preceder su adquisición.

Un mantenimiento puede programarse para el futuro; un trabajo en curso o realizado tiene fecha no futura y no anterior a la adquisición. El costo admite cero, hasta dos decimales y no es negativo. Un trabajo realizado exige resultado y actualiza la última revisión de la batería cuando es más reciente. El estado del recurso se administra de forma explícita; registrar mantenimiento no cambia automáticamente su estado operativo.

Un trabajo iniciado no puede volver a programado. Los trabajos realizados o cancelados conservan su historial sin edición/eliminación. Solo una programación puede eliminarse. Una corrección posterior a un trabajo realizado debe registrarse como un mantenimiento nuevo con explicación en observaciones.

## Polimorfismo e IDs

`mantenimientos.mantenible_id` y `mantenible_tipo` enlazan cada mantenimiento con su recurso. Se respetan los nombres no convencionales del esquema en `morphMany` y `morphTo`; no se cambia el morph map global.

El formulario nunca envía la clase PHP o el ID del padre. Se toman del modelo enlazado en la URL y de su relación. Si se envían `mantenible_id`/`mantenible_tipo`, el Request los rechaza. Un ID de mantenimiento de otro padre responde 404 para consulta, edición y eliminación, incluso si ambos padres son del mismo tipo. Los registros inexistentes también responden 404.

MantenimientoMovilidad hereda de Mantenimiento y usa la misma tabla. Permite exigir auditoría transaccional en este módulo sin cambiar la auditoría ni la Policy general de los mantenimientos de otros grupos. Los Gates están bajo `movilidad.mantenimiento.*` para no reemplazar la autorización de otros dominios.

## Historial, transacciones y auditoría

Se bloquea eliminar un dron/robot con baterías o mantenimientos y una batería con mantenimientos. Se informa al usuario que puede dar de baja el recurso. No se realizan borrados en cascada desde las pantallas del módulo. Estas restricciones también se comprueban en eventos Eloquent de los dos recursos.

GestionRecursos y GestionMantenimientos escriben dentro de transacciones y bloquean las filas relevantes. Un fallo al auditar revierte la escritura, incluido el mantenimiento y la actualización de revisión de batería. AuditaMovilidad reutiliza los eventos y la normalización del trait existente, propagando errores solo en los modelos de este módulo. Se conserva usuario, acción, registro y valores de las operaciones; contraseñas y tokens no se registran.

Las escrituras externas al módulo deben usar Eloquent y transacciones. SQL directo/query builder masivo no ejecuta eventos de modelos; la FK original de baterías sigue siendo cascadeOnDelete y las relaciones polimórficas no llevan FK. El equipo no debe borrar estos recursos directamente por SQL si desea conservar su historial. No se cambian los controles ni las pantallas de auditoría del Grupo 4.

## Layout y componentes compartidos

La rama inicial no tenía un layout común. Se propone `resources/views/layouts/app.blade.php`, `public/css/sigdet.css` y los componentes `field`, `feedback` y `delete-button`. Las vistas de ambos tipos de recurso comparten formularios/listados, y las de mantenimiento comparten la tabla de historial. No requieren compilar Vite.

El layout es reutilizable por todos los grupos e incluye una sección `navigation` extensible. Antes de integrar, el equipo debe acordar este layout o adaptar las vistas al layout común que decida usar. No se crean layouts individuales para cada entidad.

## Validación y revisión

```shell
php artisan test
php vendor/bin/pint --dirty --test
php artisan route:list --path=movilidad
```

Validación local del 14/10/2026: 20 pruebas y 198 comprobaciones aprobadas, 29 rutas registradas. Los tests cubren CRUD y responsables, filtros, datos fuera de rango, códigos/series duplicados, estados, horas/ciclos, asociaciones y fechas, polimorfismo de ambos recursos, IDs ajenos/inexistentes e inyección de tipos, permisos, cuentas/roles inactivos, preservación del historial, transiciones, auditoría y reversión, CSRF, XSS y seeder idempotente. Las pantallas se renderizaron con respuestas HTTP 200 en datos aislados.

Las pruebas usan SQLite en memoria; no modifican la base de trabajo. Queda pendiente probar la instalación y los bloqueos concurrentes con MySQL del equipo. El módulo tampoco incluye la revisión humana ni la aprobación del PR: deben realizarlas el compañero y el equipo. No se afirma que esté completada la definición institucional de terminado antes de esa revisión y de verificar el merge.

### Revisión por el compañero

1. Crear un dron y un robot; cambiar su responsable y comprobar trazabilidad.
2. Probar códigos/series duplicados, horas negativas/decrecientes y fechas inválidas.
3. Registrar una batería, cambiar su asociación y comprobar límites/ciclos/revisiones.
4. Programar y completar mantenimientos para ambos tipos de recurso; comprobar `mantenible`.
5. Sustituir el ID de mantenimiento por el de otro padre y verificar 404.
6. Probar URLs de escritura con un usuario de consulta y uno sin permisos.
7. Intentar eliminar recursos con historial y reabrir trabajos realizados/cancelados.
8. Revisar el layout común y la integración del login del Grupo 1.
9. Aplicar la migración y ejecutar pruebas con MySQL en una base de prueba.

## Integración y Git

Archivos compartidos tocados: DroneRobot y Bateria (factory, historial y auditoría obligatoria); Mantenimiento (constantes de este módulo); `routes/web.php` (include del archivo de rutas propio); `bootstrap/providers.php` (registro del provider). Las migraciones originales, Usuario, autenticación, vehículos y componentes del Grupo 4 no se modifican.

Antes de publicar, actualizar `acuña` con `testing` siguiendo el flujo acordado por el equipo, resolver cualquier conflicto comprendiendo ambos cambios y volver a probar. Nunca subir directamente a `testing`. Subir la rama personal y abrir PR hacia `testing`; el compañero debe revisar, probar y aprobar antes del merge. Verificar de nuevo después del merge.

```shell
git push origin acuña
```

Recorrido de una operación: ruta protegida → sesión/cuenta activa → Policy/Gate → FormRequest → controller → service transaccional → relación Eloquent/modelo → base/auditoría → redirect y vista con mensajes.

## Actualización de presentación — 21/10/2026

Se renovaron las vistas del Grupo 3 B con una presentación sobria de trabajo: cabecera azul oscuro, fondo gris claro, tablas con acciones compactas, estados discretos con texto, navegación del módulo y formularios agrupados por identificación, asignación y seguimiento. Los estilos de public/css/sigdet.css están limitados a .movilidad-page. El layout general conserva las rutas de seguridad y otros módulos; las páginas de vehículos del integrante A conservan su layout propio.

Se recuperó el registro de las rutas de drones y baterías, que había quedado ausente tras la integración de vehículos. Ahora viven en routes/movilidad-recursos.php, incluido desde routes/movilidad.php sin reemplazar las rutas A. Se adaptaron las expectativas de las pruebas al login general y al cierre de sesión de cuentas inactivas que ya implementó el Grupo 1.

Validación de esta actualización: 18 pruebas del Grupo 3 B y 199 comprobaciones aprobadas; ocho pantallas renderizadas con datos de prueba en SQLite en memoria; listado y formulario inspeccionados en navegador. No se alteró la base de trabajo. La suite completa tiene 98 pruebas aprobadas y 32 fallidas en otros módulos, incluidas pruebas de vehículos que referencian App\Models\User inexistente, expectativas de login anteriores a la integración, conteos de seeders y comprobaciones de permisos del Grupo 2. Esos archivos permanecen sin cambios y requieren revisión de sus responsables.
