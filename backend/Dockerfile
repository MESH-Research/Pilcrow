ARG COMPOSER_PLATFORM=linux/amd64
ARG COMPOSER_VERSION=2.8.9
ARG COMPOSER_PLATFORM=linux/amd64
ARG PHP_VERSION=8.4.15

FROM --platform=${COMPOSER_PLATFORM} composer:${COMPOSER_VERSION} AS composer
FROM php:${PHP_VERSION}-fpm AS base

ARG TARGETARCH

ARG PANDOC_VERSION=2.18
ARG COMPOSE_WAIT_VERSION=2.7.3
ARG PHP_EXTS="bcmath pdo_mysql pcntl zip intl gettext mysqli"
ARG PHP_PECL_EXTS="redis apcu xhprof"
ARG APT_PACKAGES="openssl ca-certificates bash libzip-dev libaio1t64 libncurses6 libnuma1 libxml2-dev git libfcgi-bin sqlite3 default-mysql-client libicu-dev"
#ENV COMPOSER_HOME=/tmp/composer
ENV LOG_CHANNEL=stderr

RUN  apt update && apt install -y ${APT_PACKAGES} \
    && docker-php-ext-install -j$(nproc) ${PHP_EXTS} \
    && pecl install ${PHP_PECL_EXTS} \
    && docker-php-ext-enable ${PHP_PECL_EXTS} \
    && apt purge libxml2-dev libzip-dev -y \
    && rm -rf /var/lib/apt/lists/*

RUN curl -Lo /wait https://github.com/ufoscout/docker-compose-wait/releases/download/${COMPOSE_WAIT_VERSION}/wait \
    && chmod 0755 /wait \
    && curl -Lo /usr/local/bin/mysql2sqlite https://raw.githubusercontent.com/mysql2sqlite/mysql2sqlite/refs/heads/master/mysql2sqlite \
    && chmod +x /usr/local/bin/mysql2sqlite \
    && curl -Lo /usr/local/bin/php-fpm-healthcheck https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
    && chmod 0755 /usr/local/bin/php-fpm-healthcheck \
    && curl --silent --show-error -L https://github.com/jgm/pandoc/releases/download/${PANDOC_VERSION}/pandoc-${PANDOC_VERSION}-1-${TARGETARCH}.deb -o /tmp/pandoc.deb \
    && dpkg -i /tmp/pandoc.deb \
    && rm /tmp/pandoc.deb

RUN echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini \
    && echo "error_reporting=E_ALL ^ (E_DEPRECATED | E_STRICT)" >> ${PHP_INI_DIR}/conf.d/errors.ini

RUN mkdir -p /var/www/.composer \
    && chown -R www-data:www-data /var/www/.composer \
    && chmod -R 0777 /var/www/.composer

COPY --from=composer /usr/bin/composer /usr/bin/

WORKDIR /var/www/html
USER www-data
COPY --chown=www-data composer.json composer.lock ./

#Make storage directories
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views

RUN --mount=type=cache,target=/var/www/.composer,uid=33,gid=33 \
    composer install --no-scripts --no-autoloader --prefer-dist --no-dev
COPY --chown=www-data . .
RUN composer dump-autoload -o
#Mint version into image last to limit cache busting
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE
RUN sed -i "s#env('VERSION', '')#env('VERSION', '${VERSION}')#g" config/pilcrow.php \
    && sed -i "s#env('VERSION_URL', '')#env('VERSION_URL', '${VERSION_URL}')#g" config/pilcrow.php \
    && sed -i "s#env('VERSION_DATE', '')#env('VERSION_DATE', '${VERSION_DATE}')#g" config/pilcrow.php

SHELL ["/bin/bash", "-c"]
FROM base AS fpm


FROM base AS unit-test

ENV DB_DATABASE="pilcrow"
ENV APP_ENV=testing

# Use shared PHP config - same file used by Lando (backend/php.dev.ini)
USER root
COPY php.dev.ini ${PHP_INI_DIR}/conf.d/pilcrow.ini

# Re-run composer install to add dev dependencies
USER www-data
RUN --mount=type=cache,target=/var/www/.composer,uid=33,gid=33 \
    composer install --prefer-dist

# Supply a random/cache busting timestamp to prevent caching of the test run.
ARG BUILDSTAMP
ARG DB_HOST=127.0.0.1
ENV DB_HOST=${DB_HOST}
RUN ./artisan migrate:fresh --seed

RUN touch .env && ./artisan test

FROM base AS lint

USER www-data
ARG BUILDSTAMP
RUN --mount=type=cache,target=/var/www/.composer,uid=33,gid=33 \
    composer install --prefer-dist && \
    composer lint
