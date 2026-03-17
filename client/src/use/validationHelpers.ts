import { computed, watch, unref, type Ref } from "vue"
import { clone } from "lodash"
import type { ErrorObject } from "@vuelidate/core"
import type { ApolloError } from "@apollo/client/errors"

/**
 * Create a computed property for checking for an error condition on a field.
 * Computed has the signature (field, key) where field is the field to check and
 * key is the validator or external validator message to check for.
 *
 * @inject Validator Vuelidate validation object.
 * @returns computed
 */
export const useHasErrorKey = (
  validator:
    | Ref<Record<string, { $errors: ErrorObject[] }>>
    | Record<string, { $errors: ErrorObject[] }>
) => {
  const v = unref(validator)
  return computed(() => {
    return (field: string, key: string) => {
      return hasErrorKey(v?.[field].$errors, key) ?? false
    }
  })
}

/**
 * Checks the supplied validation errors array for the presence of a validator
 * or an externalResults message.
 */
export function hasErrorKey(errors: ErrorObject[], key: string) {
  return errors.some((error) => {
    return getErrorMessageKey(error) == key
  })
}

/**
 * Returns the validator name if the error is from a local validator or the message
 * returned from the external validator if the error is external.
 */
export function getErrorMessageKey($error: ErrorObject) {
  if ($error.$validator === "$externalResults") {
    return $error.$message
  }
  return $error.$validator
}

/**
 * Remove the externalValidation errors when the field changes value.
 */
export function externalFieldWatcher(
  data: Record<string, unknown>,
  externalValidation: Record<string, string[]>,
  field: string
) {
  oneShotPropertyWatch(data, field, () => {
    externalValidation[field] = []
  })
}

/**
 * Add a one-shot watcher to a reactive property field that removes itself after
 * being called once when the property values changes.
 */
export function oneShotPropertyWatch(
  data: Record<string, unknown>,
  property: string,
  callback: () => void
) {
  const cancel = watch(
    () => clone(data),
    (data, oldValue) => {
      if (data[property] != oldValue[property]) {
        callback()
        cancel()
      }
    }
  )
}

/**
 * Parses a GraphQL error object for any validation errors and applies them to the
 * provided externalValidation reactive.  Apply the externalValidation reactive to
 * vuelidate's $externalResults option to include GraphQL validation errors in
 * vuelidate error responses.
 */
export function applyExternalValidationErrors(
  data: Record<string, unknown>,
  externalValidation: Record<string, string[]>,
  error: ApolloError | null | undefined,
  strip = ""
) {
  const gqlErrors = error?.graphQLErrors ?? []
  const validationErrors = gqlErrors
    .map((gError) => {
      const fields = (gError?.extensions?.validation ?? null) as Record<
        string,
        string[]
      > | null
      if (!fields) return null
      const errors: Record<string, string[]> = {}
      for (const [key, value] of Object.entries(fields)) {
        errors[key.replace(strip, "")] = value
      }
      return errors
    })
    .filter((e) => e)
  if (validationErrors.length === 0) {
    return false
  }
  Object.assign(externalValidation, ...validationErrors)
  for (const [key] of Object.entries(externalValidation)) {
    externalFieldWatcher(data, externalValidation, key)
  }
  return true
}
