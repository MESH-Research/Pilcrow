import DiscardChangesDialog from "src/components/dialogs/DiscardChangesDialog.vue"
import {
  computed,
  onMounted,
  onUnmounted,
  ref,
  getCurrentInstance,
  inject,
} from "vue"
import { onBeforeRouteLeave } from "vue-router"
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
import { isEmpty } from "lodash"
import { unflatten } from "flat"

export function useDirtyGuard(dirtyRef) {
  const { dialog } = useQuasar()
  function beforeUnload(e) {
    if (dirtyRef.value) {
      e.preventDefault()
      e.returnValue = ""
      return "Are you sure you want to leave this page? (Changes might be lost)"
    }
  }
  function dirtyDialog() {
    return dialog({
      component: DiscardChangesDialog,
    })
  }

  onBeforeRouteLeave(() => {
    return new Promise((resolve) => {
      if (!dirtyRef.value) {
        resolve(true)
        return
      }
      dirtyDialog()
        .onOk(function () {
          resolve(true)
        })
        .onCancel(function () {
          resolve(false)
        })
    })
  })

  onMounted(() => {
    window.addEventListener("beforeunload", beforeUnload)
  })
  onUnmounted(() => {
    window.removeEventListener("beforeunload", beforeUnload)
  })
}

export function useFormState(queryLoadingRef, mutationLoadingRef) {
  const dirty = ref(false)
  const saved = ref(false)
  const errorMessage = ref("")
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
    reset,
    setError,
  }
}

export function useVQWrap(validator, tPath) {
  const { emit } = getCurrentInstance()

  const { te, t } = useI18n()
  const parentUpdate = inject("vqupdate", null)

  const parentTPrefix = inject("tPrefix", "")

  const tPrefix = computed(() => {
    if (typeof tPath === "string") {
      return tPath
    }
    const prefix = parentTPrefix ? `${parentTPrefix}.` : ""
    return `${prefix}${validator.$path}`
  })

  function getTranslationKey(key) {
    return `${tPrefix.value}.${key}`
  }

  function getTranslation(key) {
    if (!te(getTranslationKey(key))) return ""
    return t(getTranslationKey(key))
  }

  function updateValue(newValue) {
    if (parentUpdate) {
      parentUpdate(validator, newValue)
    } else {
      emit("vqupdate", validator, newValue)
    }
  }

  const model = computed({
    get() {
      return validator.$model
    },
    set(newValue) {
      const value = newValue !== null ? newValue : ""
      updateValue(value)
    },
  })

  return { getTranslationKey, getTranslation, model }
}

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
