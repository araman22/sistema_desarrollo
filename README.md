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

- Cada persona trabaja en su rama personal y abre un Pull Request hacia `testing`.
- `testing` se usa para validar los cambios.
- Una vez aprobados, se integra `testing` en `main`.
- Cada push o merge a `main` inicia el despliegue definido en `.github/workflows/deploy-production.yml`.

Ramas personales existentes: `tapia`, `zarazaga`, `santillan`, `dominguez`, `acuña`, `albarracin`, `banegas`, `pappalardo`.

Para crear la rama de un colaborador nuevo a partir de `main`:

```sh
git checkout main
git pull
git switch -c <nombre-del-colaborador>
git push -u origin <nombre-del-colaborador>
```

## Protección de ramas

En GitHub, **Settings → Branches → Add branch protection rule** (o **Settings → Rules → Rulesets**) para `testing` y `main`:

- Require a pull request before merging.
- Require status checks when available.
- Desactivar el push directo a `main` (solo merge por PR desde `testing`).
- Marcar `testing` como la rama protegida para validar PRs de las ramas personales.

## Despliegue en Ubuntu

El servidor debe tener PHP con `pdo_pgsql`, Composer, Git y acceso a PostgreSQL. El repositorio debe estar clonado en una carpeta de despliegue y esa carpeta debe tener configurado `origin` hacia este repositorio. Para un repositorio privado, configura también una deploy key de solo lectura en GitHub.

Configuración inicial (una sola vez en la PC vieja):

```bash
# 1) Requisitos
sudo apt update
sudo apt install -y git composer php-cli php-fpm php-pgsql php-mbstring php-xml php-curl php-zip unzip nginx
php -m | grep pgsql        # debe listar pdo_pgsql

# 2) Clonar el repo en la carpeta de despliegue
sudo mkdir -p /var/www/sistema_desarrollo
sudo chown $USER:$USER /var/www/sistema_desarrollo
git clone https://github.com/araman22/sistema_desarrollo.git /var/www/sistema_desarrollo
cd /var/www/sistema_desarrollo
git branch --set-upstream-to=origin/main main

# 3) Repo privado: crear una deploy key de solo lectura
#    ssh-keygen -t ed25519 -f ~/.ssh/deploy_key -N ""
#    Agregar ~/.ssh/deploy_key.pub en GitHub → Settings → Deploy keys.
#    Luego configurar ~/.ssh/config para que este clon use la deploy_key
#    (Host github.com /   IdentityFile ~/.ssh/deploy_key /   IdentitiesOnly yes)
#    y cambiar origin a SSH: git remote set-url origin git@github.com:araman22/sistema_desarrollo.git

# 4) Ambiente de producción
cp .env.example .env
#   Editar .env: APP_ENV=production, APP_DEBUG=false, APP_URL,
#   DB_CONNECTION=pgsql con DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD
php artisan key:generate
php artisan migrate --force
php artisan optimize

# 5) Autorizar a GitHub Actions para entrar por SSH
mkdir -p ~/.ssh && chmod 700 ~/.ssh
touch ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys
#   Agregar al final de authorized_keys la clave pública del par DEPLOY_SSH_KEY.
#   Configurar nginx/php-fpm para servir /var/www/sistema_desarrollo/public (o usar
#   php artisan serve --host=0.0.0.0 como solución simple para pruebas).
```

A partir de ahí, cada push o merge a `main` ejecuta el workflow: hace `git fetch` + `git reset --hard origin/main`, instala dependencias de producción, corre migraciones y optimiza la app. No hace falta ejecutar comandos a mano en el servidor.

En GitHub, abre **Settings → Secrets and variables → Actions** y crea estos secretos:

- `DEPLOY_HOST`: IP o nombre DNS alcanzable desde GitHub Actions.
- `DEPLOY_USER`: usuario SSH del servidor.
- `DEPLOY_SSH_KEY`: clave privada SSH autorizada para ese usuario.
- `DEPLOY_PATH`: ruta absoluta al clon del proyecto en Ubuntu.
- `DEPLOY_PORT`: opcional; si falta se usa el puerto `22`.

En el servidor crea el archivo `.env` dentro de `DEPLOY_PATH` (no se versiona) y define al menos `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` y los valores de PostgreSQL (`DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Antes del primer despliegue, instala dependencias (`composer install --no-dev`) y genera una `APP_KEY` con `php artisan key:generate`; conserva ese `.env` en el servidor. La base y el usuario deben existir y aceptar conexiones desde la aplicación.

El workflow actualiza el clon a `origin/main`, instala dependencias de producción, ejecuta migraciones y optimiza Laravel. Asegúrate de que el servidor pueda recibir SSH desde GitHub Actions; una máquina detrás de un router sin acceso entrante necesitará una VPN/túnel o un runner propio dentro de tu red.

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
