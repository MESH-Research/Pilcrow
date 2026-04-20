# User Avatars & Media Storage

Pilcrow allows users to upload a profile avatar. Uploaded images are processed
with `spatie/laravel-medialibrary` and stored on a pluggable Laravel
filesystem disk, so you can keep media on the local filesystem or move it to
any S3-compatible object store without touching application code.

::: tip Accepted formats
Avatars accept `image/jpeg`, `image/png`, and `image/webp` only, with a 5 MB
upload cap. The client resizes crops to a 512 × 512 PNG before sending, and
the server generates `thumb` (96 × 96) and `medium` (256 × 256) conversions
on upload.
:::

## Local (public) disk

This is the default for a fresh install. Files are written to
`storage/app/public` inside the container and served over HTTP from
`public/storage`.

1. Confirm `MEDIA_DISK=public` (the default) in your `.env`.
2. Create the symlink once after first deploy:
   ```sh
   docker compose exec phpfpm ./artisan storage:link
   ```
3. Ensure your reverse proxy serves `/storage/` as static files (our bundled
   nginx vhost already does this).

::: warning Persistence
`storage/app/public` **must** live on a Docker volume if you want avatars to
survive container restarts. The reference compose file includes a
`storage_app_public` volume for this purpose.
:::

## S3-compatible disk

You can point media at AWS S3, DigitalOcean Spaces, Cloudflare R2, MinIO, or
any other S3-compatible bucket with no code changes.

### 1. Install the S3 driver

The S3 driver is an optional Composer package and must be installed once in
your deployment:

```sh
docker compose exec phpfpm composer require league/flysystem-aws-s3-v3
```

### 2. Configure the bucket

Grant the application an IAM user (or equivalent access key) with at minimum
`s3:PutObject`, `s3:GetObject`, `s3:DeleteObject`, and `s3:ListBucket`
permissions on the bucket. Configure the bucket for public-read on the
`avatars/` path if you want clients to load images directly, or serve
through a signed-URL CDN if you prefer to keep the bucket private.

### 3. Set environment variables

```env
MEDIA_DISK=s3

AWS_ACCESS_KEY_ID=AKIA...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=pilcrow-media
AWS_URL=https://media.example.com        # optional CDN / vanity URL
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com  # required for non-AWS providers
```

### 4. (Optional) Back-fill existing avatars

If you're switching disks after users have already uploaded, copy the
`avatars/` prefix from the source disk to the new one. `spatie/laravel-medialibrary`
stores the disk name on each media row, so you can migrate records with:

```sh
docker compose exec phpfpm ./artisan tinker
>>> App\Models\User::with('media')->each(fn ($u) => $u->getMedia('avatar')->each->move($u, 'avatar', 's3'));
```

Verify a sample of avatars load, then decommission the old disk.

## Content policy & moderation

Allowing user-uploaded images creates a content-moderation surface. At a
minimum, consider:

1. **Terms of service / acceptable use policy.** State that avatars must not
   be obscene, depict violence, impersonate others, or contain copyrighted
   material. Reserve the right to remove an avatar or suspend an account.
2. **Reporting.** Give other users a way to flag an avatar for review.
3. **Admin moderation.** Application administrators can already clear any
   user's avatar via the `deleteUserAvatar` mutation (gated by the existing
   `UserPolicy::update` ability). Consider surfacing a take-down control in
   the admin UI.
4. **Audit logging.** Pilcrow bundles `owen-it/laravel-auditing`; we
   recommend enabling it on the `Media` model so avatar add/remove events
   are captured for investigation.
5. **Automated scanning.** For larger deployments, run uploads through a
   content-moderation API (AWS Rekognition, Google Vision SafeSearch,
   Azure AI Content Safety, Sightengine, Hive Moderation, etc.) before
   marking the upload as visible. Rejected uploads should be deleted and
   the user notified.
6. **Format hardening.** Pilcrow intentionally rejects SVG and other
   vector/script-capable formats to avoid stored-XSS via crafted avatars.
   Keep this restriction in place if you customize the accepted MIME list.
7. **Rate limits.** Cap avatar uploads per user per hour to limit abuse.
