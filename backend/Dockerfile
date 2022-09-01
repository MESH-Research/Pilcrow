FROM php:8.0-fpm


RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip libzip-dev libnuma1 libaio1 libncurses6 && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

RUN curl --silent --show-error https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

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
COPY --chown=www-data:www-data . .
USER www-data:www-data
RUN composer install --no-dev
RUN chmod +x artisan
