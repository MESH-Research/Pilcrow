import { computed,  ref } from "vue"

export function useFormState(query, mutation) {
  const dirty = ref(false)
  const saved = ref(false)
  const errorMessage = ref("")
  const queryLoadingRef = query?.loading ?? null
  const mutationLoadingRef = mutation.loading
  const state = computed(() => {
    if (mutationLoadingRef.value) {
      return "saving"
    }
    if (queryLoadingRef?.value) {
      return "loading"
    }
    if (errorMessage.value) {
      return "error"
    }
    if (dirty.value) {
      return "dirty"
    }
    if (saved.value) {
      return "saved"
    }
    return "idle"
  })

  function reset() {
    dirty.value = false
    saved.value = false
    errorMessage.value = ""
  }

  function setError(message) {
    errorMessage.value = message
  }
  return {
    state,
    saved,
    dirty,
    queryLoading: queryLoadingRef,
    mutationLoading: mutationLoadingRef,
    errorMessage,
    mutationError: mutation.error,
    reset,
    setError,
  }
}

