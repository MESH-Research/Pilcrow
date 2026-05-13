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

## Telemetry

Error reporting is **disabled by default**. When enabled, the application sends
runtime errors (server and browser) to a Sentry-compatible endpoint. No
telemetry leaves the installation unless `TELEMETRY_ENABLED=true` *and*
`TELEMETRY_DSN` is set. See [Telemetry](./telemetry.md) for the full data
contract and scrubber rules.

| Parameter                              | Example / Default                  | Required | Description                                                                                                                                                                                                                                                                  |
|----------------------------------------|------------------------------------|----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| TELEMETRY_ENABLED                      | `false`                            |          | Master switch. When false, the SDKs are not initialized.                                                                                                                                                                                                                     |
| TELEMETRY_DSN                          | `https://key@sentry.example.com/1` |          | Sentry DSN used by the Laravel backend. Required when enabled.                                                                                                                                                                                                               |
| TELEMETRY_DSN_PUBLIC                   | `https://key@sentry.example.com/1` |          | DSN served to the browser. Defaults to `TELEMETRY_DSN`. Split only if backend and browser need different endpoints (e.g. container-internal vs proxy).                                                                                                                       |
| TELEMETRY_ENVIRONMENT                  | `"production"`                     |          | Tags events with environment name. Defaults to `APP_ENV`.                                                                                                                                                                                                                    |
| TELEMETRY_RELEASE                      | `"pilcrow-backend@1.4.0"`          |          | Backend release tag. Defaults to `pilcrow-backend@$VERSION` when `VERSION` is set at build time. Frontend tags itself with `pilcrow-client@$VERSION` from its own bundled `VERSION` (this lets cached client / fresh backend show as distinct releases in the issue stream). |
| TELEMETRY_TRACES_SAMPLE_RATE           | `0.0`                              |          | 0.0 disables performance tracing. 0.1 = sample 10%.                                                                                                                                                                                                                          |
| TELEMETRY_REPLAYS_SESSION_SAMPLE_RATE  | `0.0`                              |          | Browser session replay (masked) sample rate. Off by default.                                                                                                                                                                                                                 |
| TELEMETRY_REPLAYS_ON_ERROR_SAMPLE_RATE | `0.0`                              |          | Replay capture on error. Off by default.                                                                                                                                                                                                                                     |

## Source Maps

The client image is built with hidden source maps: `.map` files are emitted
alongside the bundle but the `//# sourceMappingURL=` comment is stripped from
the JS, so production users never download maps. Maps can be exposed on a
per-instance basis (e.g. to debug a hot-reproducing issue on a staging or
canary node) by toggling a single env var on the client container.

| Parameter         | Example / Default                  | Required | Description                                                                                                                                                                                                                    |
|-------------------|------------------------------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| EXPOSE_SOURCEMAPS | `false`                            |          | When `true`, nginx serves `.js.map` files and adds a `SourceMap` response header on `.js` so browser devtools can fetch the matching map. Defaults to `true` when `APP_ENV` is `local`/`dev`/`development`, otherwise `false`. |

## Redis

Redis can improve application performance by functioning as an in-memory key-value store for cached data.  To configure a redis connection:

``` env
CACHE_DRVIER=redis #change this from the default "file" cache
REDIS_HOST=redis.example.com
REDIS_PASSWORD=my secret password
REDIS_PORT=6379
```