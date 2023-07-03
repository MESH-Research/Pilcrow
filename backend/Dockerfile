FROM php:8.2-fpm
ARG VERSION
ARG VERSION_URL
ARG VERSION_DATE
ARG PHP_EXTS="bcmath pdo_mysql pcntl zip"
ARG PHP_PECL_EXTS="redis"
ARG COMPOSER_NO_DEV="1"
RUN  apt update && apt install -y openssl ca-certificates libzip-dev libaio1 libncurses6 libnuma1 libxml2-dev git \
  && docker-php-ext-install -j$(nproc) ${PHP_EXTS} \
  && pecl install ${PHP_PECL_EXTS} \
  && docker-php-ext-enable ${PHP_PECL_EXTS} \
  && apt purge libxml2-dev libzip-dev -y \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.5.5 /usr/bin/composer /usr/bin/composer

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.7.3/wait /wait
RUN chmod +x /wait

RUN curl --silent --show-error -L https://github.com/jgm/pandoc/releases/download/2.18/pandoc-2.18-1-amd64.deb -o /tmp/pandoc.deb && \
  dpkg -i /tmp/pandoc.deb && \
  rm /tmp/pandoc.deb

RUN cd /tmp && \
  curl --silent --show-error -L https://downloads.mysql.com/archives/get/p/23/file/{mysql-common,mysql-community-client}_5.7.29-1debian10_amd64.deb -O && \
  dpkg -i /tmp/mysql-common_5.7.29-1debian10_amd64.deb && \
  dpkg -i /tmp/mysql-community-client_5.7.29-1debian10_amd64.deb && \
  rm -rf /tmp/mysql*

WORKDIR /var/www/html
USER www-data:www-data
COPY --chown=www-data composer.json composer.lock ./

#Install dependencies with dev if dev arg is true

RUN composer install --no-scripts --no-autoloader --prefer-dist
COPY --chown=www-data  . .
RUN composer install --prefer-dist --no-plugins

#CREATE STORAGE DIRECTORIES
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views

#FIX PERMISSIONS
RUN chmod +x artisan
