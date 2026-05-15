import { defineBoot } from "#q-app/wrappers"
import { readTelemetryConfig, scrub } from "src/telemetry/config"

export default defineBoot(async ({ app, router }) => {
  const cfg = readTelemetryConfig()
  if (!cfg) return

  // Dynamic import so disabled installations never download the ~80 KB SDK.
  const Sentry = await import("@sentry/vue")

  const release = process.env.VERSION
    ? `pilcrow-client@${process.env.VERSION}`
    : undefined

  Sentry.init({
    app,
    dsn: cfg.dsn ?? undefined,
    environment: cfg.environment ?? undefined,
    release,
    tracesSampleRate: cfg.tracesSampleRate,
    sendDefaultPii: false,
    integrations: [Sentry.browserTracingIntegration({ router })],
    beforeSend(event) {
      if (event.request) event.request = scrub(event.request)
      if (event.extra) event.extra = scrub(event.extra)
      if (event.contexts) event.contexts = scrub(event.contexts)
      return event
    },
    beforeBreadcrumb(breadcrumb) {
      if (breadcrumb.data) breadcrumb.data = scrub(breadcrumb.data)
      return breadcrumb
    }
  })
})
