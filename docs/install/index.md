# Installation

Pilcrow is packaged as a pair of Docker containers and is intended to be deployed using Docker Compose or Kubernetes

## Prerequisites
- Modern Docker version: v23.0+
- Modern Docker Compose version: V2+
- Reverse Proxy: A reverse proxy should be used to handle incoming traffic and is required to terminate SSL.
- Mysql/MariaDB database (can be added to compose script)

## Optional
- Redis: Redis can be used to improve cache performance and enables queue workers to process jobs.

## Step-By-Step
For convenience, our Docker Compose configuration has been made available in a separate [repository.](https://github.com/mesh-research/pilcrow-docker)

1. Clone the repository
```sh
git clone https://github.com/mesh-research/pilcrow-docker [destination]
```

1. Copy the sample environment file.
```sh
cd [destination]
cp sample.env .env
```

1. Edit the sample environment file.
```env
APP_NAME=Pilcrow   # <-- Give your application a name
APP_KEY=<random string>  # <-- See below for generating a new APP_KEY
APP_URL=https://localhost  # <-- this should be the URL you'll host your app
APP_PORT=8888  # <-- Set to port the application should be exposed on.

DB_HOST=127.0.0.1 # <-- Enter your database credentials in these fields
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

MAIL_FROM_ADDRESS=<email address> # <-- This MUST be a valid email address
MAIL_FROM_NAME="Pilcrow Mailer"   # <-- Name for email sender

MAIL_MAILER=smtp  # <-- Configure your outgoing mail settings here.
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null


CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

```


::: tip Generating APP_KEY
The app key is used to encrypt session cookies and should be set to a random 32 character string.  To generate a new key, you can use the following command:
```sh
docker run -t ghcr.io/mesh-research/pilcrow/fpm:latest ./artisan key:generate --show --no-ansi
```
After running the command, be sure to copy and paste the output into your `.env` file.
:::

4. Start the stack
  ```sh
  docker compose up -d
  ```
The application should start after a few moments.  (Omit the `-d` option above to attach to the containers and monitor their log output.)

5. Point your reverse proxy at the port you listed in the `.env`.

::: warning SSL Setup
Because hosting scenarios are always different, we don't include SSL termination in the pilcrow/web container.  However, the application should absolutely be served over SSL.  We **do** provide a [receipe to add SSL support](/install/recipes/proxy) to the container stack.
:::
## Upgrading
Upgrading is a relatively simple affair.  After checking the [release notes](https://github.com/mesh-research/pilcrow/releases) for any new configuration variable that need to be set:

```sh
docker compose pull # Pull new container images
docker compose up -d # Swap the new container images for the old.
```
