# Telemetry

Pilcrow can report runtime errors from the Laravel backend and the Vue/Quasar
frontend to any Sentry-compatible endpoint. Telemetry is **off by default**.
The browser SDK is dynamically imported only when the master switch is on, so
disabled installations never download it.

This page covers what gets reported, what is scrubbed, and how to point
Pilcrow at a Sentry-compatible endpoint. Setting up that endpoint itself
(self-hosted [GlitchTip](https://glitchtip.com), Sentry SaaS, or anything
else speaking the Sentry ingest protocol) is out of scope for these docs —
follow the upstream install for whichever target you pick.

## What is reported

When enabled:

- **Unhandled exceptions** from Laravel (server) — operation name, stack trace,
  HTTP request path/method (headers and request body scrubbed).
- **Unhandled errors and promise rejections** in the browser — stack trace,
  route, URL.
- **GraphQL operation errors** — operation name, error message, and the
  variables (with sensitive fields redacted; see below).
- **Performance traces and browser session replays** only when explicitly
  sampled above zero. Both are off by default.

## What is *not* reported

The scrubber redacts the following keys before any payload leaves the
application, case-insensitively at every nesting depth:

- `password` — login + password reset variables
- `token` — email verify, password reset, and submission invite tokens
- `code` — OAuth authorization codes
- `content` — manuscript content and inline / overall comment text

Other variables (ids, ranges, style criteria, etc.) are kept so triage has
enough context to reproduce.

Session replays mask all text, mask all inputs, and block all media by default.

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

## Two-DSN split

`TELEMETRY_DSN_PUBLIC` is optional and only needed when the Laravel container
and the browser reach the endpoint via different hostnames (e.g. a
container-internal hostname vs. a host-side proxy). When unset, the browser
uses `TELEMETRY_DSN` directly.

