export type TelemetryConfig = {
  enabled: boolean
  dsn: string | null
  environment: string | null
  tracesSampleRate: number
}

declare global {
  interface Window {
    __TELEMETRY_CONFIG?: TelemetryConfig
  }
}

// Names of GraphQL variables that carry secrets or confidentiality-regime
// content (manuscript text, review comments). Field-level redaction is
// preferred over dropping entire mutation variables so non-sensitive context
// like ids, ranges, and style criteria stays available for triage.
const SENSITIVE_KEYS = new Set(["password", "token", "code", "content"])

export function readTelemetryConfig(): TelemetryConfig | null {
  if (typeof window === "undefined") return null
  const cfg = window.__TELEMETRY_CONFIG
  if (!cfg || !cfg.enabled || !cfg.dsn) return null
  return cfg
}

export function scrub<T>(value: T): T {
  if (Array.isArray(value)) {
    return value.map((v) => scrub(v)) as unknown as T
  }
  if (value && typeof value === "object") {
    const out: Record<string, unknown> = {}
    for (const [k, v] of Object.entries(value as Record<string, unknown>)) {
      if (SENSITIVE_KEYS.has(k.toLowerCase())) {
        out[k] = "[Filtered]"
      } else {
        out[k] = scrub(v)
      }
    }
    return out as T
  }
  return value
}
