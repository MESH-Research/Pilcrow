# Telemetry

Pilcrow can report runtime errors from the Laravel backend and the Vue/Quasar
frontend to any Sentry-compatible endpoint. Telemetry is **off by default**.
The browser SDK is dynamically imported only when the master switch is on, so
disabled installations never download it.

This page covers what gets reported, what is scrubbed, and how to enable a
self-hosted [GlitchTip](https://glitchtip.com) instance or a Sentry SaaS
project.

## What is reported

When enabled:

- **Unhandled exceptions** from Laravel (server) — operation name, stack trace,
  HTTP request path/method (headers and request body scrubbed).
- **Unhandled errors and promise rejections** in the browser — stack trace,
  route, URL.
- **GraphQL operation errors** — operation name, error message, and (for
  operations that don't carry free-text content) the variables.
- **Performance traces and browser session replays** only when explicitly
  sampled above zero. Both are off by default.

## What is *not* reported

The scrubber filters the following before any payload leaves the application,
case-insensitively at every nesting depth:

- `password`, `password_confirmation`, `current_password`
- `token`, `access_token`, `refresh_token`, `api_key`, `remember_token`
- `authorization`, `cookie`, `set-cookie`, `x-xsrf-token`, `xsrf-token`
- `email`, `email_address`, `phone`
- `submission_content`, `manuscript`, `body`, `content`

GraphQL variables for the following operations are dropped wholesale because
they contain unpublished manuscript text or reviewer commentary:

- `UpdateSubmissionContent`
- `CreateInlineComment` / `UpdateInlineComment`
- `CreateOverallComment` / `UpdateOverallComment`
- `SubmitReview` / `UpdateReview`

Session replays mask all text, mask all inputs, and block all media by default.

User identity is reported as the **internal user ID and role tag only** —
never email, name, or affiliation.

## Enabling it

Set in your `.env`:

```env
TELEMETRY_ENABLED=true
TELEMETRY_DSN=https://<public-key>@your-endpoint/<project-id>
TELEMETRY_ENVIRONMENT=production
```

`TELEMETRY_RELEASE` is optional — by default the backend tags events with
`pilcrow-backend@$VERSION` (where `VERSION` is baked into the image at build
time). The frontend independently tags its events with
`pilcrow-client@$VERSION` from the version baked into its bundle. The split is
deliberate: a stale cached SPA reporting against a freshly deployed backend
will show up as two distinct releases in GlitchTip, making version skew
visible in the issue stream rather than hiding it behind a single tag.

Set `TELEMETRY_RELEASE` only if you want to override the backend's default
(e.g. to tag a hotfix or pin to a known-good version).

After changing values, rebuild Laravel's config cache:

```sh
docker compose exec backend php artisan config:cache
```

The DSN value is injected into the SPA at runtime via `window.__TELEMETRY_CONFIG`,
so no client rebuild is required when the DSN rotates.

## Recipe: self-hosted GlitchTip

GlitchTip implements the Sentry ingestion protocol and is the recommended
self-hosted target. Production: deploy via the upstream Helm chart or
[`docker-compose.yaml`](https://glitchtip.com/documentation/install), point
`TELEMETRY_DSN` at the resulting endpoint, done.

For local Pilcrow development, stand up GlitchTip yourself via its upstream
install (docker-compose or helm) and point `TELEMETRY_DSN` at it. See
[`tools/glitchtip/README.md`](https://github.com/MESH-Research/Pilcrow/blob/master/tools/glitchtip/README.md) for the
local-dev workflow including the two-DSN split (`TELEMETRY_DSN` /
`TELEMETRY_DSN_PUBLIC`) used when the Laravel container and the browser reach
GlitchTip via different hostnames.

## Recipe: Sentry SaaS

Create a project in [sentry.io](https://sentry.io) of platform "Laravel"
(server) — the same DSN is reused by the Vue SDK. No code changes needed.

## Disabling telemetry per environment

Set `TELEMETRY_ENABLED=false` (or unset `TELEMETRY_DSN`). The next request
will inject `enabled: false` and the browser SDK will not be imported at all
(it is dynamically imported only when needed).

## Roadmap

Behavioural analytics (page views, feature usage) is **not** in this PR. That
work belongs in a separate change with explicit consent UX and an admin opt-in
toggle — see the project README for the planned PR sequence.
