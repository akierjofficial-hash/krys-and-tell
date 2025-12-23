#!/usr/bin/env bash
set -e

composer install --no-dev --optimize-autoloader

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php artisan migrate --force
php artisan storage:link || true
