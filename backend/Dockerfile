FROM php:8.0-fpm


RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip libzip-dev && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

RUN curl --silent --show-error https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.7.3/wait /wait
RUN chmod +x /wait

ADD https://github.com/jgm/pandoc/releases/download/2.18/pandoc-2.18-1-amd64.deb /tmp/pandoc.deb
RUN dpkg -i /tmp/pandoc.deb && rm /tmp/pandoc.deb

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
USER www-data:www-data
RUN composer install --no-dev
RUN chmod +x artisan

