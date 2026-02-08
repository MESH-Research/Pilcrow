import { computed, ref, watch, unref } from "vue"
import { isEmpty } from "lodash"
import { unflatten } from "flat"

export function useGraphQLValidation(errorRef) {
  const validationErrors = computed(() => {
    const gqlErrors = errorRef.value?.graphQLErrors ?? []
    const serverValidationErrors = {}
    gqlErrors.forEach((item) => {
      const vErrors = item?.extensions?.validation ?? false
      if (vErrors !== false) {
        for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
          serverValidationErrors[fieldName] = fieldErrors
        }
      }
    })
    return unflatten(serverValidationErrors)
  })

  const hasValidationErrors = computed(() => {
    return !isEmpty(validationErrors.value)
  })

  return { validationErrors, hasValidationErrors }
}

export function useExternalResultFromGraphQL(form, error) {
  const { validationErrors } = useGraphQLValidation(error)

  const $externalResults = ref({})

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
