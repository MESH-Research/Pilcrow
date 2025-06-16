ARG COMPOSER_VERSION=2.8.9
ARG PHP_VERSION=8.4.8
FROM composer:${COMPOSER_VERSION} AS composer
FROM php:${PHP_VERSION}-fpm AS base
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE
ARG TARGETARCH
ARG PHP_EXTS="bcmath pdo_mysql pcntl zip"
ARG PHP_PECL_EXTS="redis"
ENV COMPOSER_HOME=/tmp/composer

RUN  apt update && apt install -y openssl ca-certificates libzip-dev libaio1 libncurses6 libnuma1 libxml2-dev git libfcgi-bin \
  && docker-php-ext-install -j$(nproc) ${PHP_EXTS} \
  && pecl install ${PHP_PECL_EXTS} \
  && docker-php-ext-enable ${PHP_PECL_EXTS} \
  && apt purge libxml2-dev libzip-dev -y \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.7.3/wait /wait
RUN chmod +x /wait

ADD https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck /usr/local/bin/
RUN chmod 0755 /usr/local/bin/php-fpm-healthcheck


RUN curl --silent --show-error -L https://github.com/jgm/pandoc/releases/download/2.18/pandoc-2.18-1-${TARGETARCH}.deb -o /tmp/pandoc.deb && \
  dpkg -i /tmp/pandoc.deb && \
  rm /tmp/pandoc.deb

RUN cd /tmp && \
  curl --silent --show-error -L https://downloads.mysql.com/archives/get/p/23/file/{mysql-common,mysql-community-client}_5.7.29-1debian10_${TARGETARCH}.deb -O && \
  dpkg -i /tmp/mysql-common_5.7.29-1debian10_${TARGETARCH}.deb && \
  dpkg -i /tmp/mysql-community-client_5.7.29-1debian10_${TARGETARCH}.deb && \
  rm -rf /tmp/mysql*

RUN set -xe && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html
USER www-data:www-data
COPY --chown=www-data composer.json composer.lock ./

#Make storage directories
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views

#Install dependencies with dev if dev arg is true

RUN composer install --no-scripts --no-autoloader --prefer-dist --no-dev
COPY --chown=www-data . .
RUN composer install --prefer-dist --no-plugins

#FIX PERMISSIONS

#Copy version changes last to limit cache busting
COPY --chown=www-data config/pilcrow.php config/pilcrow.php
#Mint Version into Image
RUN sed -i "s#env('VERSION', '')#env('VERSION', '${VERSION}')#g" config/pilcrow.php && \
    sed -i "s#env('VERSION_URL', '')#env('VERSION_URL', '${VERSION_URL}')#g" config/pilcrow.php && \
    sed -i "s#env('VERSION_DATE', '')#env('VERSION_DATE', '${VERSION_DATE}')#g" config/pilcrow.php

FROM base AS unit-test

# Install SQLite3 for unit tests

RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite && \
    rm -rf /var/lib/apt/lists/*

RUN DB_CONNECTION=sqlite \
    APP_ENV=local \
    APP_DEBUG=true \
    ./artisan test

FROM base AS lint

RUN vendor/bin/phpcs
