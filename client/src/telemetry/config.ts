export type TelemetryConfig = {
  enabled: boolean
  dsn: string | null
  environment: string | null
  tracesSampleRate: number
  replaysSessionSampleRate: number
  replaysOnErrorSampleRate: number
}

declare global {
  interface Window {
    __TELEMETRY_CONFIG?: TelemetryConfig
  }
}

const SENSITIVE_OPERATIONS = new Set([
  "UpdateSubmissionContent",
  "CreateInlineComment",
  "CreateOverallComment",
  "UpdateInlineComment",
  "UpdateOverallComment",
  "SubmitReview",
  "UpdateReview"
])

const SENSITIVE_KEYS = new Set([
  "password",
  "password_confirmation",
  "current_password",
  "token",
  "access_token",
  "refresh_token",
  "api_key",
  "authorization",
  "cookie",
  "x-xsrf-token",
  "email",
  "phone",
  "submission_content",
  "manuscript",
  "body",
  "content"
])

export function readTelemetryConfig(): TelemetryConfig | null {
  if (typeof window === "undefined") return null
  const cfg = window.__TELEMETRY_CONFIG
  if (!cfg || !cfg.enabled || !cfg.dsn) return null
  return cfg
}

export function isSensitiveOperation(name: unknown): boolean {
  return typeof name === "string" && SENSITIVE_OPERATIONS.has(name)
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
