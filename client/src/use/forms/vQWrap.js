import {
  computed,
  getCurrentInstance,
  inject,
} from "vue"
import { useI18n } from "vue-i18n"


export function useVQWrap(validator, tPath) {
  const { emit } = getCurrentInstance()

  const { t } = useI18n()
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
