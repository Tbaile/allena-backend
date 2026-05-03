#!/usr/bin/env bash

set -e

export $(grep -v '^#' .env | xargs)

if [ "$1" = "php-fpm" ]; then
    composer install --no-interaction
    wait-for "${DB_HOST:?Missing DB_HOST}:${DB_PORT:?Missing DB_PORT}" -t 60
    php artisan migrate
fi

exec "$@"
