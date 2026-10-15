# SIGDET - Grupo 3, integrante A

Implementación en la rama albarracin. No se modificó la rama ni se integró testing.

## Alcance

Tipos de vehículos y vehículos: alta, listado paginado, búsqueda por dominio, filtro por estado, detalle, edición y eliminación restringida. Responsable policial activo, seguro, fechas de revisión, kilometraje y documentos privados. Mantenimiento preventivo, correctivo y revisión, con próxima fecha/kilometraje. Alertas visibles por vencimiento de seguro, revisión y mantenimiento. Las fechas dentro de 30 días producen alertas. El historial se conserva y puede corregirse; no se elimina.

No incluye drones/robots, baterías, ciclos ni mantenimientos polimórficos del integrante B.

## Dependencias de la rama

Esta rama tenía únicamente el esqueleto Laravel y no el esquema completo. Se recuperaron de la copia local de origin/testing seis migraciones sin alterarlas: personas, funciones, policías, tipos, vehículos y mantenimientos de vehículos. Las primeras tres son dependencias para relacionar responsables, no implementaciones del módulo de personal. Las migraciones existentes de jerarquías y armas permanecen intactas. ResponsablePolicial es un adaptador de lectura; no reemplaza los modelos del grupo 1.

No se consultó ni descargó el estado remoto actual de GitHub. La referencia local revisada de testing fue 369ca87. Al integrar, verificar las migraciones homónimas y utilizar las versiones acordadas por el equipo; no generar tablas duplicadas.

## Puesta en marcha

1. Comprobar la rama con `git branch --show-current` (albarracin).
2. Configurar la base MySQL en .env y ejecutar `php artisan migrate`. No utilizar migrate:fresh sobre una base con datos.
3. Ejecutar `php artisan db:seed --class=MovilidadSeeder` para el catálogo de tipos. No crea usuarios ni responsables ficticios.
4. Utilizar un usuario existente de la tabla users. Para crear uno local, usar `php artisan tinker`, `App\Models\User::create(['name'=>'Tu nombre','email'=>'tu correo','password'=>Illuminate\Support\Facades\Hash::make('tu contraseña')]);` reemplazando los valores. No usar contraseñas públicas ni subirlas a Git.
5. Autorizarlo mediante `php artisan movilidad:autorizar correo --editar`. Sin --editar obtiene solo consulta; --revocar quita el acceso. El comando falla si el usuario no existe.
6. Ejecutar `php artisan serve` y abrir `/movilidad/acceso`.
7. Para pruebas aisladas, ejecutar `php artisan test`. Usan SQLite en memoria y no modifican la base de desarrollo.

## Seguridad e integración

Policies en todas las operaciones, Form Requests, auth, CSRF y escapes Blade. El acceso local se deniega por defecto y la tabla movilidad_accesos solo se administra con un comando local. Si el usuario autenticado implementa tienePermiso/estaActivo (Usuario del grupo 1), se utilizan los permisos vehiculos.* y vehiculo_mantenimientos.*. Los tipos utilizan vehiculos.* hasta acordar un permiso específico. Las rutas anidadas tienen scopeBindings para rechazar IDs de otro vehículo. El campo vehiculo_id no se acepta del formulario.

El acceso /movilidad/acceso, el adaptador de permisos y la redirección de invitados son provisionales para esta rama antigua. Al integrar el grupo 1, usar su login, configuración auth y middleware de usuario activo; retirar el acceso local. No migrar automáticamente usuarios entre users y usuarios.

Auditoría transaccional en movilidad_auditorias: usuario, fecha, acción, tabla, ID, anterior/nuevo. No hay rutas que permitan editarla. Se necesita acordar la unión con Auditoria del grupo 4 al integrar; hoy este módulo no genera registros en su tabla compartida. Alertas calculadas en pantalla, sin notificaciones ni escritura a la tabla alertas del grupo 4. La integración con esa tabla y el layout común requiere acuerdo del equipo. El layout movilidad está aislado porque no existe layout común en esta rama.

Archivos PDF/JPG/PNG hasta 5 MB, comprobación de MIME y extensión, nombres de almacenamiento aleatorios en disco local privado y descarga autorizada como adjunto. No requiere storage:link. Se conservan adjuntos; no hay eliminación de documentos. Si falla su registro se elimina el archivo recién guardado.

El kilometraje actual nunca disminuye y los mantenimientos deben respetar el orden cronológico de kilómetros. Registrar un mantenimiento con mayor kilometraje actualiza la ficha. Los bloqueos de filas y transacciones protegen actualización del vehículo, mantenimiento, adjuntos y eliminación. Eliminar un tipo utilizado se rechaza. Eliminar un vehículo con historial o documentos se rechaza aunque la migración original de historial usa cascadeOnDelete; se debe acordar un cambio a restrictOnDelete si el equipo necesita protección adicional ante SQL directo. No se alteró esa migración compartida.

## Revisión del compañero

Probar usuario sin acceso, lector y editor; URLs directas; IDs inexistentes y de otro vehículo; responsables activos/inactivos; dominio duplicado; disminución de km; fechas y costos inválidos; archivos prohibidos o grandes; descarga sin permiso; baja con historial y conservación de auditoría. Revisar las decisiones provisionales anteriores antes de integrar. Probar también en MySQL: las pruebas automáticas usan SQLite y no verifican bloqueos concurrentes de MySQL.

La consigna exige PR revisado por el compañero y verificación después del merge. Los cambios se publican únicamente en albarracin por autorización del integrante A. El PR hacia testing y su merge quedan pendientes de revisión humana e integración acordada.

## Verificación realizada

Las pruebas se ejecutaron en una copia aislada sin el .env de desarrollo. PHP de este entorno reporta is_readable=false aunque puede leer los archivos. Se adaptó únicamente la comprobación del bootstrap de PHPUnit en la copia: existencia y lectura real del archivo. No se alteró vendor en el proyecto. Después de iniciar Laragon se aplicaron las siete migraciones pendientes a MySQL en sistema_desarrollo y se cargó MovilidadSeeder. No quedan migraciones pendientes; no se reinició ni se vació la base.

Resultado final: 33 pruebas aprobadas y 119 assertions (31 pruebas del módulo y 2 originales). Se ejecutaron migraciones en SQLite en memoria para cada prueba. Las migraciones también se aplicaron correctamente en MySQL. La rama permanece albarracin; el PR y merge hacia testing siguen pendientes.
