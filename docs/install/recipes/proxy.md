# Proxy Config (with SSL)

You can add a proxy container to the docker compose stack and also configure automatic SSL certificates.  There are solutions available for [NGINX](https://github.com/nginx-proxy/nginx-proxy), however, we'll be using [Traefik](https://doc.traefik.io/traefik/providers/docker/) for this example.

Paste the below in a `docker-compose.override.yaml` file.  Note, if you already have a `docker-compose.override.yaml` file, be sure to merge your existing file's top level keys.  This is based on Traefik's own [docker-compose with let's encrypt guide](https://doc.traefik.io/traefik/user-guides/docker-compose/acme-tls/) which goes into greater details of customizations.


``` yaml
# docker-compose.override.yaml
version: "3.3"

services:

  traefik:
    image: "traefik:v2.10"
    container_name: "traefik"
    command:
      - "--api.insecure=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.websecure.address=:443"
      - "--certificatesresolvers.le.acme.tlschallenge=true"
      #- "--certificatesresolvers.myresolver.acme.caserver=https://acme-staging-v02.api.letsencrypt.org/directory"
      - "--certificatesresolvers.le.acme.email=postmaster@example.com"
      - "--certificatesresolvers.le.acme.storage=/letsencrypt/acme.json"
    ports:
      - "443:443"
    volumes:
      - "./letsencrypt:/letsencrypt"
      - "/var/run/docker.sock:/var/run/docker.sock:ro"

  web:
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.web.rule=Host(`pilcrow.example.com`)"
      - "traefik.http.routers.web.entrypoints=websecure"
      - "traefik.http.routers.web.tls.certresolver=le"

```

- Replace `postmaster@example.com` by your own email within the `certificatesresolvers.myresolver.acme.email` command line argument of the traefik service.
- Replace `whoami.example.com` by your own domain within the `traefik.http.routers.web.rule` label of the whoami service.
- Optionally uncomment the following lines if you want to test/debug:
``` yaml
#- "--log.level=DEBUG"
#- "--certificatesresolvers.myresolver.acme.caserver=https://acme-staging-v02.api.letsencrypt.org/directory"
```
- Run `docker-compose up -d` within the folder where you created the previous file.

- Wait a bit and visit `https://your_own_domain`to confirm everything went fine.

:::warning Let's Encrypt and Rate Limiting
Note that Let's Encrypt API has rate limiting. These last up to **one week**, and can not be overridden.

When running Traefik in a container this file should be persisted across restarts. If Traefik requests new certificates each time it starts up, a crash-looping container can quickly reach Let's Encrypt's ratelimits. To configure where certificates are stored, please take a look at the storage configuration.

Use Let's Encrypt staging server with the `caServer` configuration option when experimenting to avoid hitting this limit too fast.
:::

:::tip Debugging
If you uncommented the `acme.caserver line`, you will get an SSL error, but if you display the certificate and see it was emitted by `Fake LE Intermediate X1` then it means all is good. (It is the staging environment intermediate certificate used by let's encrypt). You can now safely comment the `acme.caserver line`, remove the `letsencrypt/acme.json` file and restart Traefik to issue a valid certificate.





:::