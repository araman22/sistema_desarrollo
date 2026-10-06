# SIGDET — Sistema de Desarrollo

Documentación técnica del proyecto: qué contiene, cómo está construido y qué falta para que funcione.

- **Framework:** Laravel 12
- **PHP:** 8.2 (`^8.2`)
- **Laravel:** `^12.0`
- **Base de datos configurada:** MySQL (`sistema_desarrollo`) en `.env`; PostgreSQL en `.env.example`
- **Estado:** la capa de datos (modelos, migraciones, seeders) está completa y verificada. La capa de aplicación (rutas, controllers, vistas, autenticación) está **prácticamente vacía**.

---

## 1. Qué es el proyecto

Sistema de gestión interna para una institución policial. Centraliza el personal, su estructura jerárquica, los recursos asignados (armas, vehículos, equipamiento, drones) y los procesos administrativos (licencias, capacitaciones, proyectos, incidentes, movimientos económicos).

### Arquitectura central: `personas` vs `policias`

La decisión de diseño más importante del proyecto es la separación entre **identidad** y **ficha laboral**:

| Tabla | Rol | Contiene |
|---|---|---|
| `personas` | Identidad civil | DNI, nombre, apellido, sexo, fecha de nacimiento, domicilio, teléfono, correo |
| `policias` | Ficha laboral | `persona_id`, jerarquía, arma asignada, oficina, legajo, función, fecha de ingreso, estado, observaciones |

Consecuencias:

- `personas` **no** tiene oficina, jerarquía, arma ni función.
- Todo lo laboral apunta a `policias.id` mediante `policia_id` o `responsable_id`.
- El modelo `Usuario` (que inicia sesión) se enlaza a `policias`, no a `personas`.
- El nombre completo del policía se deriva de su persona relacionada.

Existe un ciclo intencionado entre dos tablas:

```
oficinas.responsable_id → policias.id
policias.oficina_id    → oficinas.id
```

Ambos son `SET NULL`: si se borra la persona o el policía, la oficina sobrevive sin responsable, y viceversa.

### Módulos de permisos (RBAC)

`usuarios` → `roles` → `permisos`, con tabla pivote `rol_permiso`.

- `rol_id` es `RESTRICT`: no se puede borrar un rol con usuarios asignados.
- `policia_id` es `SET NULL`: un usuario puede existir sin ficha policial.
- `Rol` define la constante `Rol::ADMINISTRADOR`.

### Auditoría

El trait `App\Models\Concerns\RegistraAuditoria` se aplica a los modelos de negocio y registra en la tabla `auditorias` las operaciones `crear`, `editar`, `eliminar` y `restaurar`, guardando:

- `usuario_id` (vía `Auth::id()`), acción, tabla y registro afectados
- `valor_anterior` y `valor_nuevo` como JSON
- `direccion_ip` y `created_at` (el momento de la acción auditada)

La auditoría nunca debe romper la operación principal: los errores se registran con `Log::error()` y se silencian. Las contraseñas (`contrasena`, `password`, `remember_token`) se excluyen del volcado. El modelo `Auditoria` no usa el trait, para evitar recursión.

### Timestamps

Todas las tablas de negocio usan los timestamps convencionales de Laravel, `created_at` y `updated_at`, declarados con `$table->timestamps()` en las migraciones. **Ningún modelo necesita `const CREATED_AT` / `const UPDATED_AT`**, ni castear esas columnas: Eloquent las gestiona y las devuelve como `Carbon`.

Las únicas excepciones son las tablas puente, que sí desactivan los timestamps porque no registran cuándo se modificaron:

| Modelo | Tabla |
|---|---|
| `RolPermiso` | `rol_permiso` |
| `CapacitacionPersonal` | `capacitacion_personal` |
| `ProyectoIntegrante` | `proyecto_integrantes` |

> **Ojo con las inserciones masivas.** `insertOrIgnore()` e `insert()` del query builder **no** agregan timestamps. Hay que usar `fillAndInsertOrIgnore()` (o `upsert()`) para que Eloquent los rellene. `PermisoSeeder` usa `fillAndInsertOrIgnore()` por este motivo.

---

## 2. Estructura del proyecto

```
app/
  Http/Controllers/     11 archivos, todos vacíos o stub (ver §5)
  Models/               32 modelos Eloquent
  Models/Concerns/      RegistraAuditoria (trait de auditoría)
  Providers/            AppServiceProvider vacío
bootstrap/app.php       sin middleware ni excepciones registradas
config/auth.php         provider 'users' → App\Models\Usuario
database/migrations/    35 migraciones
database/seeders/       11 seeders (2 no registrados)
resources/views/        3 vistas (2 vacías)
routes/web.php          solo la ruta '/'
tests/                  solo ExampleTest
```

### Modelos (32)

`Actividad`, `Alerta`, `Arma`, `ArmaModelo`, `Auditoria`, `Bateria`, `Capacitacion`, `CapacitacionPersonal`, `Documento`, `DroneRobot`, `EquipamientoTecnologico`, `Funcion`, `IncidenciaInfraestructura`, `Inventario`, `Jerarquia`, `Licencia`, `Mantenimiento`, `MovimientoEconomico`, `Oficina`, `Permiso`, `Persona`, `Policia`, `Proyecto`, `ProyectoIntegrante`, `Rol`, `RolPermiso`, `TipoDocumento`, `TipoLicencia`, `TipoVehiculo`, `Usuario`, `Vehiculo`, `VehiculoMantenimiento`.

### Catálogos

Cinco tablas de catálogo, todas con `nombre` único (en `armas_modelos`, `marca` + `modelo` compuestos) y los timestamps convencionales `created_at` / `updated_at`:

| Tabla | Campos de datos | Consumida por |
|---|---|---|
| `jerarquias` | `nombre` | `policias.jerarquia_id` |
| `funciones` | `nombre` | `policias.funcion_id` |
| `armas_modelos` | `marca`, `modelo`, `calibre` | `armas.modelo_arma_id` |
| `tipo_documentos` | `nombre` | `documentos.tipo_documento_id` |
| `tipo_licencias` | `nombre` | `licencias.tipo_licencia_id` |
| `tipo_vehiculos` | `nombre` | `vehiculos.tipo_vehiculo_id` |

Políticas de borrado de esas FK:

- `RESTRICT`: `policias.funcion_id`, `documentos.tipo_documento_id`, `licencias.tipo_licencia_id`, `usuarios.rol_id`. No se puede borrar un valor de catálogo que esté en uso.
- `SET NULL`: `armas.modelo_arma_id`, `vehiculos.tipo_vehiculo_id`. Son opcionales.

### Relaciones polimórficas

Dos pares polimórficos siguen en pie (a diferencia de `documentos`, que dejó de serlo):

- `alertas` → `alertable_id` / `alertable_tipo`
- `mantenimientos` → `mantenible_id` / `mantenible_tipo`

### `documentos` no representa archivos

Decisión de diseño: la tabla `documentos` es un registro documental simple (`tipo_documento_id`, `nombre`, `numero`, `descripcion`, `fecha`). **No** almacena rutas, tamaños, tipos MIME ni usuarios que subiieron el archivo. Se eliminaron las 12 relaciones `documentos()` polimórficas que quedaban colgando de los demás modelos. Si más adelante se requieren adjuntos, hay que agregar almacenamiento y columnas nuevas.

---

## 3. Migraciones

35 migraciones. El orden importa: cada catálogo se crea antes que las tablas que lo referencian.

```
0001_01_01_000000_create_users_table.php        usuarios + password_reset_tokens + sessions
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2026_09_28_130000_create_personas_table.php
2026_09_28_131725_create_jerarquias_table.php
2026_09_28_132000_create_funciones_table.php
2026_09_28_132100_create_armas_modelos_table.php
2026_09_28_132458_create_armas_table.php
2026_09_28_133000_create_policias_table.php
2026_10_05_000001_create_roles_table.php
2026_10_05_000002_create_permisos_table.php
2026_10_05_000003_create_tipo_documentos_table.php
2026_10_05_000004_create_oficinas_table.php
2026_10_05_000005_create_tipo_licencias_table.php
2026_10_05_000006_create_tipo_vehiculos_table.php
2026_10_05_000007_create_rol_permiso_table.php
2026_10_05_000008_create_documentos_table.php
2026_10_05_000009_create_licencias_table.php
2026_10_05_000010_create_capacitaciones_table.php
2026_10_05_000011_create_capacitacion_personal_table.php
2026_10_05_000013..000026  actividades, inventarios, equipamientos,
                          vehiculos, mantenimientos de vehiculo, drones,
                          baterias, proyectos, integrantes, incidencias,
                          movimientos, mantenimientos, auditorias, alertas
2026_10_05_000500_create_usuarios_table.php     FKs e índices sobre usuarios
```

Dos notas sobre las migraciones:

**1. La tabla `usuarios` no se crea donde parece.** `2026_10_05_000500_create_usuarios_table.php` solo agrega FKs e índices; la tabla (junto con `password_reset_tokens` y `sessions`) la crea la migración base del framework `0001_01_01_000000_create_users_table.php`, que fue reescrita para usar el nombre `usuarios` en lugar de `users`. Esto es necesario porque `.env` define `SESSION_DRIVER=database`.

**2. `unsignedBigInteger()->constrained()` descarta la FK en silencio en SQLite.** Solo `foreignId()` devuelve la `ForeignIdColumnDefinition` que Laravel compila correctamente en SQLite. Cuatro FKs (`armas.modelo_arma_id`, `documentos.tipo_documento_id`, `licencias.tipo_licencia_id`, `vehiculos.tipo_vehiculo_id`) se estaban creando sin ninguna restricción. Todas usan `foreignId()` ahora.

> **Regla:** para toda FK usar `$table->foreignId('x_id')->constrained('tabla')`, nunca `unsignedBigInteger()`.

**3. Los timestamps se declaran con `$table->timestamps()`.** Antes cada tabla usaba `timestamp('fecha_creacion')->useCurrent()` y `timestamp('fecha_actualizacion')->useCurrent()`, lo que obligaba a declarar `const CREATED_AT` y `const UPDATED_AT` en los 31 modelos y a castear esas dos columnas a mano. Ahora se usa el par `created_at` / `updated_at` estándar. En `auditorias`, el `fecha_hora` se reemplazó por `created_at`.

**Como las migraciones existentes fueron editadas, en bases ya instaladas hay que usar `php artisan migrate:fresh`.**

---

## 4. Seeders

`DatabaseSeeder` ejecuta, en este orden:

| # | Seeder | Contenido |
|---|---|---|
| 1 | `FuncionSeeder` | 8 funciones |
| 2 | `ArmaModeloSeeder` | 5 modelos de arma |
| 3 | `TipoDocumentoSeeder` | 7 tipos |
| 4 | `TipoLicenciaSeeder` | 5 tipos |
| 5 | `TipoVehiculoSeeder` | 7 tipos |
| 6 | `PermisoSeeder` | catálogo de permisos |
| 7 | `RolSeeder` | roles, incluido el administrador |
| 8 | `UsuarioAdminSeeder` | usuario `admin` |

El orden es obligatorio: los catálogos deben existir antes que el administrador, que los consume con `firstOrFail()`.

`UsuarioAdminSeeder` crea con `updateOrCreate()` la oficina *Dirección General*, la jerarquía *Oficial*, el arma `SN-0001`, la persona con DNI `00000000`, su ficha policial y el usuario `admin`. Es idempotente: se puede volver a ejecutar sin duplicar.

**Dos seeders existen pero no están registrados** en `DatabaseSeeder`: `JerarquiaSeeder` y `ArmaSeeder`. Ambos tienen el cuerpo vacío. No son necesarios hoy (el seeder del administrador crea la jerarquía con `updateOrCreate`), pero conviene borrarlos o implementarlos para que no confutan.

---

## 5. Faltantes

### 5.1 Crítico — la autenticación no funciona

Es el problema más grave. `Auth::attempt()` devuelve **siempre `false`**, con cualquier credencial. Dos causas encadenadas:

**a) Falta la clave `password` en `config/auth.php`.** El bloque `passwords.users` existe pero no declara la columna:

```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
        'expire' => 60,
        'throttle' => 60,
        // falta: 'password' => 'contrasena'
    ],
],
```

Verificado: `config('auth.passwords.users.password')` devuelve `""` (cadena vacía), por lo que `getAuthPasswordName()` devuelve `""` y `getAuthPassword()` devuelve `null`.

**b) El modelo usa `contrasena`, Laravel busca `password`.** La columna es `usuarios.contrasena` y el modelo la castea con `'contrasena' => 'hashed'`, pero `Usuario` **no sobreescribe** `getAuthPasswordName()`.

Solución recomendada (no tocar el config global):

```php
// app/Models/Usuario.php
public function getAuthPasswordName(): string
{
    return 'contrasena';
}
```

Alternativamente, añadir `'password' => 'contrasena'` en `config/auth.php`.

### 5.2 Crítico — recuperación de contraseña incompatible

`password_reset_tokens` tiene `email` como clave primaria, pero el modelo `Usuario` no tiene columna `email`: el correo está en `correo_electronico`. El broker de contraseñas busca por `email`, así que `Password::sendResetLink()` no encontrará al usuario.

Solución: sobreescribir en `Usuario`:

```php
public function getEmailForPasswordReset(): string
{
    return $this->correo_electronico;
}
```

Y enviar los enlaces a esa dirección.

### 5.3 Crítico — no hay rutas

`routes/web.php` tiene **una sola ruta**: `/`, que devuelve la vista `welcome` por defecto de Laravel. No existe `routes/auth.php` ni `routes/api.php`.

Falta definir: login/logout, y el CRUD de los 32 modelos.

### 5.4 Controllers vacíos o stub

Los 11 controllers existen pero **ninguno tiene una sola línea de lógica**:

| Controller | Estado |
|---|---|
| `PersonaController` | archivo de 0 bytes |
| `PoliciaController` | archivo de 0 bytes |
| `ArmaController` | stub generado |
| `ArmaModeloController` | stub generado |
| `FuncionController` | stub generado |
| `JerarquiaController` | stub generado |
| `TipoDocumentoController` | stub generado |
| `TipoLicenciaController` | stub generado |
| `TipoVehiculoController` | stub generado |
| `Controller` | 77 bytes (clase vacía, normal) |

Los stubs contienen los 7 métodos CRUD con el cuerpo `//`.

### 5.5 Vistas vacías

| Vista | Estado |
|---|---|
| `welcome.blade.php` | vista por defecto de Laravel (82 KB), sin relación con el proyecto |
| `Auth/login.blade.php` | HTML con `<body>` vacío |
| `Policia/index.blade.php` | HTML con `<body>` vacío, título con typo (*"La Politcia!"*) |

No hay vistas de create/edit/show para ningún modelo.

### 5.6 Sin autorización

No hay capa de permisos aplicada:

- No hay Policies ni Gates.
- No hay `authorize()` ni comprobaciones `can()` en ningún lado.
- `bootstrap/app.php` tiene el closure `withMiddleware()` y el de `withExceptions()` completamente vacíos: no hay alias, ni middleware de rol, ni manejo de 403.
- La auditoría registra *quién* hizo *qué*, pero no *qué puede* hacer.

El sistema tiene el catálogo de permisos completo y funcional, simplemente no se usa.

### 5.7 Sin validación de entrada

No existe el directorio `app/Http/Requests`. No hay ninguna validación, ni reglas, ni `FormRequest`. Los modelos se crearán directamente desde el controller sin filtrar entrada.

### 5.8 Sin pruebas

`tests/` solo tiene `ExampleTest`. No hay ni un test de.feature o unit. `phpunit.xml` está sin configurar (cache y base de datos de testing sin definir).

Dado que la capa de datos es la parte sólida del proyecto, es donde más conviene invertir: las migraciones, las FK y las relaciones ya están verificadas manualmente.

### 5.9 Frontend sin empezar

- `resources/js/app.js` = 22 bytes
- `resources/css/app.css` = 390 bytes

Tailwind 4 y Vite están instalados y configurados, pero no se usa ninguna clase ni componente. No hay `resources/js/components`, ni Alpine.js, ni Livewire.

### 5.10 Configuración de base de datos inconsistente

| Archivo | Motor |
|---|---|
| `.env` | `mysql`, puerto 3306, base `sistema_desarrollo` |
| `.env.example` | `pgsql`, puerto 5432, base `laravel` |
| `README.md` | dice «PostgreSQL configurado como motor predeterminado» |

Quien clonee el repositorio y use `.env.example` obtiene PostgreSQL, que **nunca se ha probado** en este proyecto. Las FK y el orden de migraciones sí son portables, pero conviene unificar.

### 5.11 Comentario desactualizado

`database/migrations/2026_10_05_000500_create_usuarios_table.php` menciona en su docblock que corre después de `create_policias (2026_10_05_000460)`. Ese nombre de migración ya no existe: ahora es `2026_09_28_133000_create_policias_table.php`.

### 5.12 Contraseña del administrador en el código

`UsuarioAdminSeeder` fija `Hash::make('password')`. Está bien para desarrollo, pero conviene leerla de una variable de entorno y no dejarla en el repositorio.

### 5.13 Otros detalles menores

- Sin API ni Sanctum: si se necesita, falta `routes/api.php` e instalar Sanctum.
- Sin factories: solo existe `UsuarioFactory`. Los seeders usan `updateOrCreate`/`firstOrFail` en lugar de factories, lo cual impide generar datos de prueba realistas.
- Columnas enumeradas como `varchar` libre: `sexo`, `estado`, `prioridad`, `tipo` están en texto sin restricciones ni enums de PHP. `Rol` sí usa constantes, el resto no.
- No hay `README` de instalación real: el `README.md` actual es el de Laravel con una sección «Proyecto» agregada.

---

## 6. Estado verificado

Lo que sí se comprobó con ejecución real (SQLite, por ausencia de MySQL en el entorno):

| Comprobación | Resultado |
|---|---|
| Orden de migraciones (simula MySQL) | 35 migraciones, ninguna referencia una tabla futura o inexistente |
| Claves foráneas reales (`PRAGMA foreign_key_list`) | FKs creadas y apuntando a tablas existentes, con su política de borrado |
| Relaciones Eloquent contra el esquema | 0 rotas |
| Modelos: casts, `$fillable` y columnas reales | 0 incoherencias |
| Timestamps `created_at` / `updated_at` | los rellena Eloquent sin ayuda; `created_at` no cambia al editar |
| `PermisoSeeder` (inserción masiva) | rellena los timestamps; 0 nulos |
| `migrate:fresh --seed` | correcto |
| `migrate:reset` | rollback limpio, queda solo `migrations` |
| Sintaxis PHP (`php -l`) | 0 errores |
| Pint | passed |
| Regresión funcional | OK (relaciones, cascadas, `RESTRICT`, auditoría) |

> **Limitación:** MySQL no está disponible en este entorno. SQLite **no valida** las FK al crearlas (acepta referencias a tablas inexistentes sin error y solo falla al insertar datos, con errno 150). Por eso el orden de migraciones y la integridad referencial se validaron con comprobaciones estáticas y `PRAGMA`, no con el motor real. La primera ejecución sobre MySQL sigue siendo obligatoria.

### Comandos

```bash
php artisan migrate:fresh --seed    # recrear y poblar
vendor\bin\pint --test             # estilo de código
vendor\bin\pint                    # aplicar estilo
php artisan migrate:reset          # revertir todo
```

---

## 7. Orden de trabajo sugerido

1. **Arreglar la autenticación** (§5.1 y §5.2) — sin esto no hay sesión ni nada usable.
2. **Definir las rutas**: `routes/auth.php` más el grupo `auth` para el CRUD.
3. **Middleware de rol** en `bootstrap/app.php`, con Policies o Gates apoyándose en el catálogo de permisos existente.
4. **CRUD de `Persona` y `Policia`**, que son el núcleo y ya tienen vistas creadas.
5. **`FormRequest`** para validar la entrada desde el primer controller.
6. **CRUD de los 5 catálogos**, que son las tablas más simples.
7. **Vistas reales**, empezando por reemplazar `welcome.blade.php`.
8. **Tests**, al menos de la capa de datos que ya está verificada a mano.
9. **Unificar `.env` / `.env.example` / `README.md`** y probar sobre el motor definitivo.
