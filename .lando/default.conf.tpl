server {

    listen 80 default_server;
    listen 443 ssl;

    server_name localhost;

    ssl_certificate           /certs/cert.crt;
    ssl_certificate_key       /certs/cert.key;
    ssl_verify_client         off;

    ssl_session_cache    shared:SSL:1m;
    ssl_session_timeout  5m;

    ssl_ciphers  HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers  on;

    port_in_redirect off;
    client_max_body_size 100M;

    root "{{LANDO_WEBROOT}}";
    index index.php index.html index.htm;

     location / {
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
		proxy_set_header Host $http_host;
		proxy_set_header X-NginX-Proxy true;
		proxy_http_version 1.1;
		proxy_set_header Upgrade $http_upgrade;
		proxy_set_header Connection "upgrade";
		proxy_max_temp_file_size 0;
		proxy_pass http://client:8080;
		proxy_redirect off;
		proxy_read_timeout 240s;
    }

    location /graphql {
        error_page 404 = @backend;
        log_not_found off;
    }

    location /graphql-playground {
        error_page 404 = @backend;
        log_not_found off;
    }

    location /sanctum/csrf-cookie {
        error_page 404 = @backend;
        log_not_found off;
    }

    location @backend {
        try_files $uri /index.php =404;
        fastcgi_pass fpm:9000;
        fastcgi_index index.php;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    location /.well-known/acme-challenge/ {
        root /var/www/letsencrypt/;
        log_not_found off;
    }
}
