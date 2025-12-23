#!/usr/bin/env bash
set -e

# Ensure Laravel writable dirs exist
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

composer install --no-dev --optimize-autoloader

# Clear any old caches
php artisan optimize:clear || true

# Run migrations
php artisan migrate --force
php artisan db:seed --force


# Rebuild caches
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php artisan storage:link || true
