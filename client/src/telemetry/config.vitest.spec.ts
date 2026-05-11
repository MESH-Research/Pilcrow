import { afterEach, describe, expect, it } from "vitest"
import { isSensitiveOperation, readTelemetryConfig, scrub } from "./config"

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
      Email: "u@x.com",
      headers: { Authorization: "Bearer x", "User-Agent": "pilcrow" },
      nested: [{ password: "p", ok: 1 }]
    }) as Record<string, unknown>

    expect(out.Email).toBe("[Filtered]")
    expect((out.headers as Record<string, string>).Authorization).toBe(
      "[Filtered]"
    )
    expect((out.headers as Record<string, string>)["User-Agent"]).toBe(
      "pilcrow"
    )
    expect((out.nested as Array<Record<string, unknown>>)[0]!.password).toBe(
      "[Filtered]"
    )
    expect((out.nested as Array<Record<string, unknown>>)[0]!.ok).toBe(1)
  })

  it("returns primitives unchanged", () => {
    expect(scrub(42)).toBe(42)
    expect(scrub("hello")).toBe("hello")
    expect(scrub(null)).toBe(null)
  })
})

describe("isSensitiveOperation", () => {
  it("flags free-text content mutations", () => {
    expect(isSensitiveOperation("UpdateSubmissionContent")).toBe(true)
    expect(isSensitiveOperation("CreateInlineComment")).toBe(true)
    expect(isSensitiveOperation("SubmitReview")).toBe(true)
  })

  it("does not flag benign reads", () => {
    expect(isSensitiveOperation("CurrentUser")).toBe(false)
    expect(isSensitiveOperation(undefined)).toBe(false)
  })
})
