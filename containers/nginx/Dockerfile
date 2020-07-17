FROM nginx:latest

RUN apt-get update -yy && \
    apt-get install -yy \
        openssl && \
    rm -rf /var/lib/apt/lists/*

ENV NGINX_SSL_KEY /etc/nginx/ssl/snakeoil.key
ENV NGINX_SSL_CERT /etc/nginx/ssl/snakeoil.crt
ENV NGINX_DHPARAM /etc/nginx/ssl/dhparam.pem
ENV NGINX_DHPARAM_SIZE 4096

RUN mkdir -p /etc/nginx/ssl

COPY default.conf.template /etc/nginx/templates/default.conf.template

COPY 10-ssl.sh /docker-entrypoint.d/10-ssl.sh