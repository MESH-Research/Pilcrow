import { afterEach, describe, expect, it } from "vitest"
import { readTelemetryConfig, scrub } from "./config"

const setCfg = (cfg: unknown) => {
  ;(window as unknown as { __TELEMETRY_CONFIG?: unknown }).__TELEMETRY_CONFIG =
    cfg
}

describe("readTelemetryConfig", () => {
  afterEach(() => {
    setCfg(undefined)
  })

  it("returns null when config is absent", () => {
    setCfg(undefined)
    expect(readTelemetryConfig()).toBeNull()
  })

  it("returns null when enabled=false even if a DSN is present", () => {
    setCfg({
      enabled: false,
      dsn: "https://k@example.ingest.sentry.io/1",
      environment: "production",
      tracesSampleRate: 0,
      replaysSessionSampleRate: 0,
      replaysOnErrorSampleRate: 0
    })
    expect(readTelemetryConfig()).toBeNull()
  })

  it("returns null when enabled=true but DSN missing", () => {
    setCfg({
      enabled: true,
      dsn: null,
      environment: "production",
      tracesSampleRate: 0,
      replaysSessionSampleRate: 0,
      replaysOnErrorSampleRate: 0
    })
    expect(readTelemetryConfig()).toBeNull()
  })

  it("returns the config when enabled and DSN are both set", () => {
    const cfg = {
      enabled: true,
      dsn: "https://k@example.ingest.sentry.io/1",
      environment: "staging",
      tracesSampleRate: 0.25,
      replaysSessionSampleRate: 0,
      replaysOnErrorSampleRate: 0.1
    }
    setCfg(cfg)
    expect(readTelemetryConfig()).toEqual(cfg)
  })
})

describe("scrub", () => {
  it("redacts sensitive keys at any depth, case-insensitively", () => {
    const out = scrub({
      Password: "p",
      input: { content: "manuscript body", id: "1" },
      nested: [{ token: "t", code: "c", ok: 1 }]
    }) as Record<string, unknown>

    expect(out.Password).toBe("[Filtered]")
    const input = out.input as Record<string, unknown>
    expect(input.content).toBe("[Filtered]")
    expect(input.id).toBe("1")
    const nested = (out.nested as Array<Record<string, unknown>>)[0]!
    expect(nested.token).toBe("[Filtered]")
    expect(nested.code).toBe("[Filtered]")
    expect(nested.ok).toBe(1)
  })

  it("leaves non-sensitive keys untouched", () => {
    const out = scrub({
      email: "u@x.com",
      submission_id: "42",
      from: 0,
      to: 10
    }) as Record<string, unknown>

    expect(out.email).toBe("u@x.com")
    expect(out.submission_id).toBe("42")
    expect(out.from).toBe(0)
    expect(out.to).toBe(10)
  })

  it("returns primitives unchanged", () => {
    expect(scrub(42)).toBe(42)
    expect(scrub("hello")).toBe("hello")
    expect(scrub(null)).toBe(null)
  })
})
