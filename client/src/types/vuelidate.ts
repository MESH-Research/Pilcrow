import type { ErrorObject } from "@vuelidate/core"

/**
 * Simplified Vuelidate validator interface for use in component props.
 *
 * The full BaseValidation type from @vuelidate/core uses complex conditional
 * types that conflict with Vue's prop type resolution, so we define just the
 * properties actually used by VQInput/VQWrap components.
 */
export interface VuelidateValidator {
  $model: unknown
  $path: string
  $error: boolean
  $errors: ErrorObject[]
  $dirty: boolean
  $touch: () => void
  $reset: () => void
}
