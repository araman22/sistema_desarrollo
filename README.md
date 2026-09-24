<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Proyecto

Base inicial en Laravel 12, con PostgreSQL configurado como motor predeterminado. Las credenciales se configuran en `.env`; nunca deben subirse al repositorio.

## Flujo de ramas

- Cada persona trabaja en una rama propia y abre un Pull Request hacia `testing`.
- `testing` se usa para validar los cambios.
- Una vez aprobados, se integra `testing` en `main`.
- Cada push o merge a `main` inicia el despliegue definido en `.github/workflows/deploy-production.yml`.

## Despliegue en Ubuntu

El workflow usa un **runner propio de GitHub Actions** instalado en el servidor. Así el servidor se conecta hacia GitHub y no hace falta abrir SSH entrante ni configurar el router, aunque el servidor y los colaboradores estén en redes distintas.

1. En GitHub, entra al repositorio en **Settings → Actions → Runners → New self-hosted runner**, elige Linux y sigue los comandos que GitHub muestra para descargar y registrar el runner. Esos comandos incluyen un token temporal: ejecútalos directamente en Ubuntu y no los compartas.
2. Configura el runner como servicio (`svc.sh install` y `svc.sh start`) con un usuario dedicado sin permisos de administrador. Ese usuario debe poder escribir en la carpeta de despliegue y en `storage` y `bootstrap/cache`.
3. Instala en Ubuntu PHP con `pdo_pgsql`, Composer y Git. Clona el repositorio en una ruta fija. El `origin` debe permitir al usuario del runner hacer `git fetch` (para un repositorio privado, usa una deploy key de solo lectura).
4. En **Settings → Secrets and variables → Actions**, crea el secreto `DEPLOY_PATH` con la ruta absoluta del clon, por ejemplo `/var/www/sistema_desarrollo`.
5. En ese clon prepara `.env` (no se versiona), con `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` y PostgreSQL (`DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Si PostgreSQL corre en la misma máquina, `DB_HOST=127.0.0.1`; si corre en otra, usa su dirección privada alcanzable desde Ubuntu. Instala dependencias con `composer install --no-dev`, genera la clave con `php artisan key:generate` y conserva `.env` en el servidor.

Al integrar cambios en `main`, el runner actualiza el clon, instala dependencias, ejecuta migraciones y optimiza Laravel. Mantén el repositorio privado y limita las modificaciones de workflows y la integración a personas de confianza: un workflow puede ejecutar comandos en el servidor donde está instalado el runner.

Este despliegue solo conecta GitHub Actions con el servidor para actualizar el código. Para que los empleados vean la aplicación desde otras redes, después habrá que habilitar acceso web, por ejemplo mediante VPN o un dominio con HTTPS y un proxy inverso.

## Desarrollo local

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Edita `.env` para indicar el host, base, usuario y contraseña de PostgreSQL local.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
