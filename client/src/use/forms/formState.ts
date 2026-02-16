import {
  computed,
  ref,
  type ComputedRef,
  type InjectionKey,
  type Ref
} from "vue"

export type FormStateStatus =
  | "saving"
  | "loading"
  | "error"
  | "dirty"
  | "saved"
  | "idle"

export interface FormState {
  state: ComputedRef<FormStateStatus>
  saved: Ref<boolean>
  dirty: Ref<boolean>
  queryLoading: Ref<boolean> | null
  mutationLoading: Ref<boolean>
  errorMessage: Ref<string>
  mutationError: Ref<Error | null>
  reset: () => void
  setError: (message: string) => void
}

export const formStateKey: InjectionKey<FormState> = Symbol("formState")

interface QueryLike {
  loading: Ref<boolean>
}

interface MutationLike {
  loading: Ref<boolean>
  error?: Ref<Error | null>
}

export function useFormState(
  query: QueryLike | null,
  mutation: MutationLike
): FormState {
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

  function setError(message: string) {
    errorMessage.value = message
  }
  return {
    state,
    saved,
    dirty,
    queryLoading: queryLoadingRef,
    mutationLoading: mutationLoadingRef,
    errorMessage,
    mutationError: mutation.error ?? ref(null),
    reset,
    setError
  }
}
