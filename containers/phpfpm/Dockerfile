
FROM php:7.4-fpm

RUN docker-php-ext-install pdo_mysql

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip && \
    rm -rf /var/lib/apt/lists/*

RUN curl --silent --show-error https://getcomposer.org/installer | php

RUN mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer

WORKDIR /var/www/html
