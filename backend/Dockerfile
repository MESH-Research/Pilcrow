ARG COMPOSER_VERSION=2.8.9
FROM composer:${COMPOSER_VERSION} AS composer
FROM php:8.4.8-fpm
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

RUN --mount=type=cache,target=/tmp/composer,uid=33,gid=33 composer install --no-scripts --no-autoloader --prefer-dist --no-dev
COPY --chown=www-data . .
RUN --mount=type=cache,target=/tmp/composer,uid=33,gid=33 composer install --prefer-dist --no-plugins


#CREATE STORAGE DIRECTORIES
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views

#FIX PERMISSIONS
RUN chmod +x artisan scripts/docker/reseed.sh

#Copy version changes last to limit cache busting
COPY --chown=www-data config/pilcrow.php config/pilcrow.php
#Mint Version into Image
RUN sed -i "s#env('VERSION', '')#env('VERSION', '${VERSION}')#g" config/pilcrow.php && \
    sed -i "s#env('VERSION_URL', '')#env('VERSION_URL', '${VERSION_URL}')#g" config/pilcrow.php && \
    sed -i "s#env('VERSION_DATE', '')#env('VERSION_DATE', '${VERSION_DATE}')#g" config/pilcrow.php
