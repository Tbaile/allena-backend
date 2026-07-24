ARG PHP_EXTENSIONS="pdo_pgsql pgsql"
ARG ADDITIONAL_PACKAGES="bash postgresql17-client"

## Base images

FROM php:8.5.4-fpm-alpine AS php_base
WORKDIR /var/www/html
COPY --from=docker.io/composer:2.9.5 /usr/bin/composer /usr/local/bin/composer
COPY --from=docker.io/mlocati/php-extension-installer:2.10 /usr/bin/install-php-extensions /usr/local/sbin/install-php-extensions
ADD --chmod=777 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for

FROM nginx:1.29.6-alpine AS nginx_base
WORKDIR /var/www/html
COPY containers/nginx/config /etc/nginx
ADD --chmod=755 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for
COPY --chmod=777 containers/nginx/90-wait-fpm.sh /docker-entrypoint.d/90-wait-fpm.sh
HEALTHCHECK CMD curl --fail --silent --output /dev/null http://localhost/status

## Utils and dependencies building

FROM php_base AS vendor
COPY composer.json .
COPY composer.lock .
RUN composer install --no-dev --no-scripts --no-cache --ignore-platform-reqs

FROM vendor AS vendor_dev
RUN composer install --no-scripts --no-cache --ignore-platform-reqs

## Production targets

FROM php_base AS php_production
ARG PHP_EXTENSIONS
RUN install-php-extensions ${PHP_EXTENSIONS}
ARG ADDITIONAL_PACKAGES
RUN apk add --no-cache ${ADDITIONAL_PACKAGES}
COPY containers/php/config/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
ARG COMPOSER_ALLOW_SUPERUSER=1
COPY --from=vendor /var/www/html/composer.json .
COPY --from=vendor /var/www/html/composer.lock .
COPY --from=vendor /var/www/html/vendor vendor
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY resources resources
COPY routes routes
COPY --chown=www-data:www-data storage storage
COPY artisan .
RUN composer check-platform-reqs \
    && composer dump-autoload --optimize \
    && echo -n "opcache.max_accelerated_files=" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo $(find . -name "*.php" | wc -l | awk '{print (int($1/1000)+2)*1000}') >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && rm /usr/local/bin/composer \
    && rm /usr/local/sbin/install-php-extensions
VOLUME ["/var/www/html/storage"]
COPY --chmod=777 containers/php/prod-entrypoint.sh /usr/local/sbin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

FROM nginx_base AS nginx_production

## Development targets

FROM php_base AS php_development
ARG PHP_EXTENSIONS
RUN install-php-extensions ${PHP_EXTENSIONS} xdebug
ARG ADDITIONAL_PACKAGES
RUN apk add --no-cache \
      ${ADDITIONAL_PACKAGES} \
        git \
        shadow \
        sudo
ARG UID
ARG GID
RUN usermod -u "$UID" -s /bin/bash www-data \
    && groupmod -g "$GID" www-data \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && echo "ALL ALL=(ALL:ALL) NOPASSWD: ALL" > "/etc/sudoers.d/password_less_sudo"
USER www-data
COPY --chmod=777 containers/php/dev-entrypoint.sh /usr/local/sbin/entrypoint
ENV XDEBUG_MODE=develop,debug,coverage,profile
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

FROM nginx_base AS nginx_development

## Testing targets

FROM php_base AS testing
ARG PHP_EXTENSIONS
RUN install-php-extensions ${PHP_EXTENSIONS} xdebug
ARG ADDITIONAL_PACKAGES
RUN apk add --no-cache ${ADDITIONAL_PACKAGES}
COPY --from=vendor_dev /var/www/html/composer.json .
COPY --from=vendor_dev /var/www/html/composer.lock .
COPY --from=vendor_dev /var/www/html/vendor vendor
COPY app app
COPY bootstrap bootstrap
COPY config config
COPY database database
COPY resources resources
COPY routes routes
COPY --chown=www-data:www-data storage storage
COPY tests tests
COPY artisan .
COPY phpunit.xml .
COPY .env.example .env.testing
RUN composer check-platform-reqs \
    && composer dump-autoload --optimize \
    && php artisan key:generate --env=testing
USER www-data
CMD ["php", "artisan", "test"]
