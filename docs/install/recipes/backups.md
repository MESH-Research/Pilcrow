# Backups

Pilcrow bundles [spatie/laravel-backup](https://spatie.be/docs/laravel-backup), which dumps the database (and, optionally, uploaded files) into a single zip and writes it to one or more storage disks. You drive it with the standard `backup:run` artisan command — there is no Pilcrow-specific wrapper, so anything the package documents works here.

Run artisan inside the `fpm` container. For a running Docker Compose stack:

```sh
docker compose exec fpm php artisan backup:run
```

The examples below show the artisan command; prefix it with `docker compose exec fpm` (or your platform's equivalent) to run it against your stack.

## Configuration

Backups are configured entirely through environment variables (see `config/backup.php` for the full set):

| Variable | Default | Purpose |
| --- | --- | --- |
| `BACKUP_DISKS` | `local` | Comma-separated destination disk(s). Each must exist in `config/filesystems.php`. |
| `BACKUP_FILE_INCLUDE` | `storage/app` | Comma-separated paths included when files are backed up. |
| `BACKUP_NOTIFICATION_EMAIL` | unset | Address that receives failure / unhealthy-backup mail. |
| `BACKUP_ARCHIVE_PASSWORD` | unset | If set, archives are AES-encrypted with this password. |

The MySQL dump uses `--single-transaction` (configured in `config/database.php`), so it produces a consistent snapshot on InnoDB without locking the live application.

## Recipe: local backup

Out of the box `BACKUP_DISKS=local`, so no extra configuration is needed. Database only:

```sh
php artisan backup:run --only-db
```

Database **and** files (everything in `BACKUP_FILE_INCLUDE`):

```sh
php artisan backup:run
```

The zip lands under the `local` disk's root (`storage/app/<APP_NAME>/`). Copy it off the host or mount that path to a volume you control.

## Off-host destinations

The application only decides **what** gets backed up; **where** the archive lands is a deployment concern. Point `BACKUP_DISKS` at any disk your deployment defines in `config/filesystems.php` (an S3 bucket, DigitalOcean Spaces, MinIO, SFTP, …) and provide that disk's credentials there. The S3 driver is bundled, so an S3-compatible target needs only a disk definition and credentials — typically supplied as deployment environment, or via a [mounted config](#fully-custom-configuration-volume-mount) when you need options the env doesn't cover.

```sh
# whatever disk name the deployment configured
php artisan backup:run --only-db --only-to-disk=offsite
```

Set `BACKUP_DISKS=local,offsite` to write to several disks every run. Give the backup destination its own bucket and scoped credentials — don't reuse the application's upload or mail (SES) credentials.

::: warning Managing backups is your responsibility
Pilcrow does not delete old backups automatically — `backup:run` only creates them. spatie provides a cleanup command (`php artisan backup:clean`) and a default cleanup strategy under the `cleanup` key in `config/backup.php`, but nothing schedules it for you. If you automate cleanup, make sure your plan accounts for what happens to the resulting files (and prefer a storage-side lifecycle rule where the storage layer can enforce retention even if the app is down).
:::

## Scheduling

`backup:run` is a plain artisan command — schedule it however your platform prefers:

- **Cron / Compose:** a cron entry (or a sidecar) that runs `docker compose exec -T fpm php artisan backup:run --only-db`.
- **Kubernetes:** a `CronJob` running the same command against the `fpm` image.
- **Before a deploy/migration:** run it as a pre-migration step so a failed dump aborts before any schema change. `backup:run` exits non-zero on failure, so the caller can gate on its exit code.

::: tip Avoid overlapping runs
spatie's `backup:run` doesn't lock against itself. If overlapping runs are possible, guard the invocation at the scheduler level (a Kubernetes `CronJob` with `concurrencyPolicy: Forbid`, or `flock` in a cron wrapper).
:::

## Restoring

A backup is a normal zip containing the SQL dump (and any included files). To restore the database, unzip it and feed the dump back in:

```sh
unzip <backup>.zip
docker compose exec -T database mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < db-dumps/mysql-laravel.sql
```

## Notifications

By default Pilcrow emails on **failed** backups and **unhealthy** (missing/stale) backups, and stays quiet on success. Set `BACKUP_NOTIFICATION_EMAIL` to receive them. Slack/Discord/webhook channels are available in `config/backup.php` if you prefer.

## Fully custom configuration (volume mount)

The `BACKUP_*` variables cover the common cases. When the deployment needs something they don't expose — a custom destination disk, an SFTP target, several buckets, per-disk options — mount your own config file over the one baked into the image instead of changing code. The app root in the container is `/var/www/html`, and config is **not** cached, so a mounted file takes effect on the next run.

```yaml
# docker-compose.override.yaml
services:
  fpm:
    volumes:
      - ./backup.php:/var/www/html/config/backup.php:ro
      # add a custom disk for the backup destination:
      - ./filesystems.php:/var/www/html/config/filesystems.php:ro
```

Copy the in-repo `config/backup.php` (and `config/filesystems.php`) as a starting point, edit freely, and reference your disks via `BACKUP_DISKS` / `--only-to-disk`. Anything valid in those files works — they're plain Laravel config.

::: tip If you enable config caching
This image doesn't cache config. If your deployment runs `php artisan config:cache`, run `php artisan config:clear` (or re-cache) after changing a mounted file so the new values load.
:::

## Further customization

`config/backup.php` is the standard spatie config file, so anything in the package's documentation applies unchanged — compression, archive encryption, custom dump options, monitoring/health checks, and cleanup strategies. See the [spatie/laravel-backup docs](https://spatie.be/docs/laravel-backup) for the full reference.
