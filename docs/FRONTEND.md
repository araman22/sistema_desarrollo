# Frontend compartido de SIGDET

## Diseño general — 21/10/2026

El layout `layouts.app` carga siempre `public/css/sigdet.css`. El sistema visual usa la clase `sigdet-app`; `movilidad-page` se conserva para ajustes del módulo. La URL del CSS incluye la fecha de modificación del archivo para evitar estilos antiguos en caché. No requiere Vite para esta hoja estática.

Se aplican cabecera institucional, navegación según las Policies existentes, tarjetas, tablas, campos, botones y mensajes a las vistas que extienden este layout. Las vistas con layouts independientes requieren su integración específica. El dashboard muestra los mismos datos existentes, con tarjetas de recursos, resumen de caja y accesos autorizados. El formulario de login conserva sus rutas, campos, CSRF, autocompletado y validaciones.

No se modificaron controladores, modelos, rutas, permisos ni reglas de negocio en esta actualización. Los menús no sustituyen la autorización del servidor.

Validación: dashboard y oficinas comprobados visualmente sobre el servidor local; pruebas de MovilidadTest y ModuloSigdetGrupo2Test: 23 aprobadas y 2 fallidas. Los dos fallos de permisos de Grupo 2 ya existían antes de esta actualización y quedan a cargo de Seguridad/Backend. Las 18 pruebas de MovilidadTest pasan.

Las cuentas sin acceso a un módulo no ven sus enlaces. Los contadores del dashboard conservan el contrato de datos del backend; su alcance y filtrado corresponden a Backend/Seguridad.
