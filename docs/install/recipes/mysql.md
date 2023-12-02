# Mysql From Docker

To add mysql to the Docker Compose stack, paste the below in a `docker-compose.override.yaml` file.  Note, if you already have a `docker-compose.override.yaml` file, be sure to merge your existing file's top level keys.

```yaml
#  docker-compose.override.yaml

services:
  # ... other services
  db:
    image: mysql:8
    environment:
      - MYSQL_RANDOM_ROOT_PASSWORD: yes
      - MYSQL_USER: ${DB_USERNAME}
      - MYSQL_PASSWORD: ${DB_PASSWORD}
      - MYSQL_DATABASE: ${DB_DATABASE}
    volumes:
      - mysql:/var/lib/mysql
volumes:
  - mysql

```

Also, you'll need to edit your .env file:

``` env
# .env
DB_HOST=db     # update these values
DB_PORT=3306   #

```