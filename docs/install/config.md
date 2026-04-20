# Configuration

Reference for environment variables.

## Basic

| Parameter | Example / Default     | Required | Description                                                        |
| --------- | --------------------- | -------- | ------------------------------------------------------------------ |
| APP_URL   | `https://mysite.com/` | ✅       | The URL the application will be hosted from, including the scheme. |
| APP_NAME  | `"My Pilcrow Site"`   | ✅       | The name of your application instance.                             |
| APP_KEY   | `base64:xdfsd9f...`   | ✅       | A 32 char string used to encrypt data in the application.          |
| APP_ENV   | `"prod"`              |          | The environment the application is running under.                  |
| APP_DEBUG | false                 |          | Enable debug logging / output                                      |


## Database <Badge type="warning" text="required" />

The application requires a Mysql database connection to store data.  The following values are used to configure the application's connection to the database.

``` env
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=my_db
DB_USERNAME=my_db_username
DB_PASSWORD=my_db_super_secret_password
```

## Outgoing Mail <Badge type="warning" text="required" />

The application has to be able to send transactional email (notifications, password resets, confirmations, etc).  Mail from address and mail from name **must** be configured, no matter which email provider you are using.

``` env
MAIL_FROM_NAME="Hello from Pilcrow"
MAIL_FROM_ADDRESS="myemail@example.com"
```

The actual connection to send email can be an SMTP connection (which a number of transactional email providers make available).

```env
MAIL_HOST=smtp.example.com
MAIL_PORT=1025
MAIL_USERNAME=my_username@example.com
MAIL_PASSWORD=my_super_secret_password
MAIL_ENCRYPTION=true
```

Laravel also [supports a number of  mail services](https://laravel.com/docs/10.x/mail#driver-prerequisites).  While we aren't able to provide support for each mailer service, feel free to open a ticket if you find a problem with non-smtp options.

## User Avatars / Media Storage

User-uploaded avatars are stored via a pluggable storage disk so you can keep them on the local filesystem or move them to an S3-compatible bucket without code changes.

| Parameter  | Example / Default | Required | Description                                                                                                |
| ---------- | ----------------- | -------- | ---------------------------------------------------------------------------------------------------------- |
| MEDIA_DISK | `public`          |          | Filesystem disk used by `spatie/laravel-medialibrary`. Set to `s3` to use an S3-compatible bucket.         |

When using the default `public` disk you must create the `public/storage` symlink once:

```sh
docker compose exec phpfpm ./artisan storage:link
```

See the [Avatars](/install/recipes/avatars/) recipe for full setup, including S3 and moderation considerations.

## Redis

Redis can improve application performance by functioning as an in-memory key-value store for cached data.  To configure a redis connection:

``` env
CACHE_DRVIER=redis #change this from the default "file" cache
REDIS_HOST=redis.example.com
REDIS_PASSWORD=my secret password
REDIS_PORT=6379
```