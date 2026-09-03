# Despliegue Google Cloud Run

Esta aplicacion quedo preparada para subir a Google Cloud Run con Laravel 13, PHP 8.4, Apache y Cloud SQL MySQL.

## 1. Servicios recomendados

- Cloud Run: aplicacion web.
- Cloud SQL MySQL 8: base de datos.
- Artifact Registry: imagen Docker.
- Secret Manager: `APP_KEY` y clave de base de datos.
- Cloud Build: compilacion y despliegue.

## 2. Variables que debes definir

Reemplaza estos valores por los reales:

```bash
PROJECT_ID="tu-proyecto-gcp"
REGION="southamerica-west1"
SERVICE="comercializadora-alimentos"
REPOSITORY="alimentos"
CLOUDSQL_INSTANCE="$PROJECT_ID:$REGION:alimentos-mysql"
DB_DATABASE="alimentos_mascotas"
DB_USERNAME="alimentos_app"
APP_URL="https://tu-dominio-o-url-cloud-run"
```

## 3. Activar APIs

```bash
gcloud services enable run.googleapis.com
gcloud services enable cloudbuild.googleapis.com
gcloud services enable artifactregistry.googleapis.com
gcloud services enable sqladmin.googleapis.com
gcloud services enable secretmanager.googleapis.com
```

## 4. Crear repositorio Docker

```bash
gcloud artifacts repositories create alimentos \
  --repository-format=docker \
  --location=southamerica-west1 \
  --description="Imagenes Comercializadora Alimentos"
```

## 5. Crear Cloud SQL

```bash
gcloud sql instances create alimentos-mysql \
  --database-version=MYSQL_8_0 \
  --tier=db-f1-micro \
  --region=southamerica-west1 \
  --storage-type=SSD \
  --storage-size=20GB \
  --backup-start-time=04:00 \
  --availability-type=zonal

gcloud sql databases create alimentos_mascotas \
  --instance=alimentos-mysql

gcloud sql users create alimentos_app \
  --instance=alimentos-mysql \
  --password="CAMBIA_ESTA_CLAVE"
```

Para produccion real, sube `--availability-type=regional` y define una clave fuerte.

## 6. Crear secretos

Genera una clave Laravel local:

```bash
php artisan key:generate --show
```

Crea los secretos:

```bash
printf "base64:TU_APP_KEY_GENERADA" | gcloud secrets create laravel-app-key --data-file=-
printf "CLAVE_DB_SEGURA" | gcloud secrets create alimentos-db-password --data-file=-
```

Da permiso al servicio de Cloud Run para leerlos:

```bash
PROJECT_NUMBER="$(gcloud projects describe $PROJECT_ID --format='value(projectNumber)')"
SERVICE_ACCOUNT="$PROJECT_NUMBER-compute@developer.gserviceaccount.com"

gcloud secrets add-iam-policy-binding laravel-app-key \
  --member="serviceAccount:$SERVICE_ACCOUNT" \
  --role="roles/secretmanager.secretAccessor"

gcloud secrets add-iam-policy-binding alimentos-db-password \
  --member="serviceAccount:$SERVICE_ACCOUNT" \
  --role="roles/secretmanager.secretAccessor"
```

## 7. Permisos Cloud SQL para Cloud Run

```bash
gcloud projects add-iam-policy-binding $PROJECT_ID \
  --member="serviceAccount:$SERVICE_ACCOUNT" \
  --role="roles/cloudsql.client"
```

## 8. Build y deploy

Desde la carpeta `alimentos-laravel13`:

```bash
gcloud builds submit \
  --config=cloudbuild.yaml \
  --substitutions=_REGION=southamerica-west1,_SERVICE=comercializadora-alimentos,_REPOSITORY=alimentos,_IMAGE=comercializadora-alimentos,_CLOUDSQL_INSTANCE="$CLOUDSQL_INSTANCE",_APP_URL="$APP_URL",_DB_DATABASE="$DB_DATABASE",_DB_USERNAME="$DB_USERNAME",_RUN_MIGRATIONS=true,_RUN_SEEDERS=false
```

La primera subida debe usar `_RUN_MIGRATIONS=true`. Despues puedes dejarlo en `false` y ejecutar migraciones de forma controlada cuando haya cambios.

## 9. Variables de entorno incluidas en Cloud Run

El `cloudbuild.yaml` configura:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `LOG_CHANNEL=stderr`
- `DB_CONNECTION=mysql`
- `DB_SOCKET=/cloudsql/PROJECT:REGION:INSTANCE`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `RUN_MIGRATIONS`
- `RUN_SEEDERS`

Secretos:

- `APP_KEY`
- `DB_PASSWORD`

## 10. Verificacion

```bash
gcloud run services describe comercializadora-alimentos \
  --region=southamerica-west1 \
  --format="value(status.url)"
```

Rutas a probar:

- `/`
- `/login`
- `/contabilidad`
- `/tienda`
- `/api/auth/token`

Usuario contabilidad local/demo:

- `contabilidad@alimentos.local`
- `12345678`

En produccion cambia las claves iniciales inmediatamente.

