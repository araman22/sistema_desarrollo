# Evidencia de control de acceso

Prueba manual sobre el módulo **Oficinas**, escribiendo la URL a mano (sin usar los botones de la interfaz).
Usuarios de prueba de `UsuariosPruebaSeeder` (contraseña `password`).

| # | Usuario (rol) | Acción | Permiso necesario | Resultado esperado | Resultado obtenido | Captura |
|---|---|---|---|---|---|---|
| 1 | invitado (`invitado`) | Abrir `/oficinas/create` **antes** del arreglo | `oficinas.crear` | 403 | ❌ Se abrió el formulario | [ver](evidencia/1-antes-sin-permiso-oficinas-create.png) |
| 2 | jperez (`encargado_oficina`) | Abrir `/oficinas/1/edit` | `oficinas.editar` | Permitido | ✅ Permitido | [ver](evidencia/2-despues-jperez-oficinas-edit-permitido.png) |
| 3 | rdiaz (`invitado`) | Abrir `/oficinas/create` **después** del arreglo | `oficinas.crear` | 403 | ✅ 403, sin mostrar datos | [ver](evidencia/3-despues-rdiaz-oficinas-create-403.png) |
| 3b | rdiaz (`invitado`) | Sesión iniciada durante la prueba 3 | — | Menú solo con módulos permitidos | ✅ Solo Oficinas e Inventario | [ver](evidencia/3b-rdiaz-logueado-menu-invitado.png) |

El rol de cada usuario está definido en `database/seeders/UsuariosPruebaSeeder.php`.

## Conclusión
Antes del arreglo, ocultar el botón con `@can` no impedía entrar escribiendo la URL.
Con `can:` en `routes/web.php` (ver `rutas-protegidas.md`), el servidor verifica el permiso en cada acción.
Los tests automáticos de `tests/Feature/SeguridadAccesoModulosTest.php` repiten estas pruebas.