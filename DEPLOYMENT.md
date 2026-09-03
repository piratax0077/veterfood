# Comercializadora Alimentos - Laravel 13 / PHP 8.4

Sistema operativo para centro de distribucion de alimentos de mascotas.

## Requisitos del servidor

- PHP 8.4 o superior.
- Extensiones PHP: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `ctype`, `json`, `fileinfo`, `xml`.
- Composer 2.
- MySQL 8 o MariaDB compatible.
- El document root del sitio debe apuntar a la carpeta `public`.

## Instalacion en servidor

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Variables importantes en `.env`

```env
APP_NAME="Comercializadora Alimentos"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.cl

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alimentos
DB_USERNAME=usuario
DB_PASSWORD=clave_segura

MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.cl
MAIL_PORT=587
MAIL_USERNAME=correo@tu-dominio.cl
MAIL_PASSWORD=clave_correo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=ventas@tu-dominio.cl
MAIL_FROM_NAME="${APP_NAME}"
```

## Accesos demo

Todos usan clave `12345678` en ambiente local/demo.

- Admin: `admin@alimentos.local`
- Central ventas: `central@alimentos.local`
- Vendedor: `vendedor@alimentos.local`
- Cliente mensual: `cliente@alimentos.local`
- Cliente semanal: `cliente.semanal@alimentos.local`
- Repartidor: `repartidor@alimentos.local`

## Escritorios

- Tienda: `/tienda`
- Login: `/login`
- Administracion: `/admin`
- Central de ventas: `/central-ventas`
- Repartidor web: `/repartidor/pedidos`
- Tracking publico: `/tracking/{codigo}`

## Modulos incluidos

- Locales de venta.
- Vendedores asignados a local.
- Clientes con una o varias mascotas.
- Productos.
- Bodegas.
- Existencias por bodega/local.
- Stock critico y stock objetivo.
- Pedidos unicos, semanales y mensuales.
- Generacion operativa de pedidos desde planes recurrentes.
- Rutas de reparto.
- Repartidores.
- API para app repartidor con pedidos, GPS, cambios de estado y foto de entrega.
- Emails de confirmacion y actualizacion de entrega.

## App repartidor

La app Cordova esta en:

`C:\Users\Jaime\Desktop\proyectos\alimentos\repartidor-veterchile`

En local apunta a:

`http://127.0.0.1:8013/api`

En servidor, cambiar en `www/index.html`:

```js
const API = 'https://tu-dominio.cl/api';
```

## Nota local

En este computador el runtime PHP 8.5.7 quedo con `pdo_mysql` habilitado y el sistema Laravel 13 apunta a la base local:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alimentos_mascotas
DB_USERNAME=root
DB_PASSWORD=
```

La base `alimentosvet` queda disponible para integracion externa/fabrica segun el documento original.

## Despliegue en Google Cloud Run

El proyecto incluye archivos listos para Google Cloud:

- `Dockerfile`
- `cloudbuild.yaml`
- `.dockerignore`
- `.gcloudignore`
- `docker/entrypoint.sh`
- `docker/apache/000-default.conf`
- `docs/google_cloud_run_deploy.md`

Guia completa:

`docs/google_cloud_run_deploy.md`
