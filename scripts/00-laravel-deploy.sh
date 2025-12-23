#!/usr/bin/env bash
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

composer install --no-dev --optimize-autoloader

php artisan optimize:clear || true

php artisan migrate --force
php artisan db:seed --force

# 👇 PRINT seeded user check into Render logs (no shell needed)
php artisan users:check-seeds || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php artisan storage:link || true
