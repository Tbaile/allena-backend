#!/usr/bin/env sh

set -e

if [ "$1" = "php-fpm" ]; then
    php artisan optimize
    wait-for "${DB_HOST:?Missing DB_HOST}:${DB_PORT:?Missing DB_PORT}" -t 60
    php artisan migrate --force
fi

exec "$@"
