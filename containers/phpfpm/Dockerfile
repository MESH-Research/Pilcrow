
FROM php:7.4-fpm


RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip libzip-dev && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

RUN curl --silent --show-error https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    composer global require hirak/prestissimo



WORKDIR /var/www/html
