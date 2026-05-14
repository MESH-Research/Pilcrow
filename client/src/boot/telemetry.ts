import { defineBoot } from "#q-app/wrappers"
import { readTelemetryConfig, scrub } from "src/telemetry/config"

export default defineBoot(async ({ app, router }) => {
  const cfg = readTelemetryConfig()
  if (!cfg) return

  // Dynamic import so disabled installations never download the ~80 KB SDK.
  const Sentry = await import("@sentry/vue")

  // Sentry's browserTracingIntegration declares its own narrow VueRouter
  // shape (params: Record<string, string | string[]>). With typed routes
  // enabled via unplugin-vue-router, Quasar's Router exposes params as
  // GenericParams (string | number), so the structural types diverge even
  // though the runtime contract is satisfied.
  type SentryRouter = Parameters<
    typeof Sentry.browserTracingIntegration
  >[0]["router"]

  const release = process.env.VERSION
    ? `pilcrow-client@${process.env.VERSION}`
    : undefined

  Sentry.init({
    app,
    dsn: cfg.dsn ?? undefined,
    environment: cfg.environment ?? undefined,
    release,
    tracesSampleRate: cfg.tracesSampleRate,
    replaysSessionSampleRate: cfg.replaysSessionSampleRate,
    replaysOnErrorSampleRate: cfg.replaysOnErrorSampleRate,
    sendDefaultPii: false,
    integrations: [
      Sentry.browserTracingIntegration({
        router: router as unknown as SentryRouter
      }),
      Sentry.replayIntegration({
        maskAllText: true,
        maskAllInputs: true,
        blockAllMedia: true
      })
    ],
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
