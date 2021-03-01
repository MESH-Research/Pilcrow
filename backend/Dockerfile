
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

WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html

RUN chmod +x .docker/* && cp .docker/* /usr/local/bin

