import type { Ref } from "vue"
import type { ApolloError } from "@apollo/client/errors"
import { computed, ref, watch, unref } from "vue"
import { isEmpty } from "lodash"
import { unflatten } from "flat"

/** Nested validation errors returned by Laravel Lighthouse */
export interface ValidationErrors {
  [key: string]: string[] | ValidationErrors
}

export function useGraphQLValidation(
  errorRef: Ref<ApolloError | null | undefined>
) {
  const validationErrors = computed<ValidationErrors>(() => {
    const gqlErrors = errorRef.value?.graphQLErrors ?? []
    const serverValidationErrors: Record<string, string[]> = {}
    gqlErrors.forEach((item) => {
      const vErrors = (item?.extensions?.validation ?? false) as
        | Record<string, string[]>
        | false
      if (vErrors !== false) {
        for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
          serverValidationErrors[fieldName] = fieldErrors
        }
      }
    })
    return unflatten(serverValidationErrors) as ValidationErrors
  })

  const hasValidationErrors = computed(() => {
    return !isEmpty(validationErrors.value)
  })

  return { validationErrors, hasValidationErrors }
}

export function useExternalResultFromGraphQL(
  form: Ref<Record<string, unknown>> | Record<string, unknown>,
  error: Ref<ApolloError | null | undefined>
) {
  const { validationErrors } = useGraphQLValidation(error)

  const $externalResults = ref<ValidationErrors>({})

  watch(validationErrors, (newValue) => {
    $externalResults.value = newValue
  })

  const clearErrors = () => ($externalResults.value = {})

  watch(
    () => unref(form),
    () => {
      clearErrors()
    }
  )

  return { $externalResults, clearErrors }
}
