# Redis

Redis is a key-value store that is often used as a backend for application cache's.  Pilcrow can make use of a redis cache and you can add one to your docker-compose setup with relative ease. Paste the below in a `docker-compose.override.yaml` file.  Note, if you already have a `docker-compose.override.yaml` file, be sure to merge your existing file's top level keys.

``` yaml
# docker-compose.override.yaml
services:
  redis:
    image: redis:latest
```

Edit your .env file:
``` env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
```

## Security Considerations

For the ease of accessing Redis from other containers via Docker networking, the "Protected mode" is turned off by default. This means that if you expose the port outside of your host, it will be open without a password to anyone. It is highly recommended to set a password (by supplying a config file) if you plan on exposing your Redis instance to the internet. For further information, see the following links about Redis security:

- [Redis documentation on security](https://redis.io/topics/security)
- [Protected mode](https://redis.io/topics/security#protected-mode)
- [A few things about Redis security by antirez](http://antirez.com/news/96)


## Persistence

Our config doesn't add any persistence for the redis container.  Meaning, that the container will lose its cache each time it restarts.  This can cause problems and probably isn't the best configuration for a production environment.  For
more information, see Redis's [documentation on persistence](http://redis.io/topics/persistence).