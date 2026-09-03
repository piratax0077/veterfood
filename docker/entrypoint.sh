#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
    if [ "${RUN_SEEDERS:-false}" = "true" ]; then
        php artisan db:seed --force
    fi
fi

php artisan storage:link >/dev/null 2>&1 || true
php artisan config:cache
php artisan view:cache

exec apache2-foreground
