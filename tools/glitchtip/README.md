# Local GlitchTip for telemetry development

GlitchTip implements the Sentry ingest protocol, so it works as a drop-in
target for the `TELEMETRY_DSN` Pilcrow accepts. It is **not** part of the
Pilcrow lando stack — bring your own instance when you need to exercise the
telemetry path end-to-end.

## Stand it up

Follow the upstream install for whichever footprint suits your machine:

- Docker Compose: <https://glitchtip.com/documentation/install#docker-compose>
- Helm / Kubernetes / source: <https://glitchtip.com/documentation/install>

Any reachable endpoint works — the Sentry ingest contract is the only
requirement.

## Wire Pilcrow to it

Register an account, create an organization, then create a project. Pick
platform **Generic JavaScript** — the same DSN works for Laravel and the Vue
SDK because both speak the Sentry ingest protocol.

Copy the DSN into `backend/.env.local`:

```env
TELEMETRY_ENABLED=true
TELEMETRY_DSN=https://<public-key>@<your-glitchtip-host>/<project-id>
TELEMETRY_ENVIRONMENT=local
TELEMETRY_RELEASE=pilcrow@dev
```

If the backend container and the browser reach GlitchTip via different
hostnames (e.g. container-internal hostname vs lando proxy), set the public
variant as well:

```env
TELEMETRY_DSN_PUBLIC=https://<public-key>@<browser-reachable-host>/<project-id>
```

Clear config cache:

```sh
lando ssh -s appserver -c "cd /app/backend && php artisan config:clear"
```

## Trigger a test event

Backend:

```sh
lando ssh -s appserver -c "cd /app/backend && php artisan tinker"
> throw new \Exception('telemetry smoke test');
```

Browser: load any Pilcrow page, open devtools console:

```js
window.__TELEMETRY_CONFIG          // confirm enabled:true and dsn populated
throw new Error('client smoke test')
```

Both events should land in the GlitchTip project's Issues tab within a few
seconds.
