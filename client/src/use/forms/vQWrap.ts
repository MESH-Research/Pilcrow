import { computed, getCurrentInstance, inject } from "vue"
import { useI18n } from "vue-i18n"
import type { VuelidateValidator } from "src/types/vuelidate"

type VQUpdateFn = (validator: VuelidateValidator, value: unknown) => void

export function useVQWrap(
  validator: VuelidateValidator,
  tPath?: string | boolean
) {
  const { emit } = getCurrentInstance()!

  const { t } = useI18n()
  const parentUpdate = inject<VQUpdateFn | null>("vqupdate", null)

  const parentTPrefix = inject<string>("tPrefix", "")

  const tPrefix = computed(() => {
    if (typeof tPath === "string") {
      return tPath
    }
    const prefix = parentTPrefix ? `${parentTPrefix}.` : ""
    return `${prefix}${validator.$path}`
  })

  function getTranslationKey(key: string) {
    return `${tPrefix.value}.${key}`
  }

  function getTranslation(key: string) {
    return t(getTranslationKey(key))
  }

  function updateValue(newValue: unknown) {
    if (parentUpdate) {
      parentUpdate(validator, newValue)
    } else {
      emit("vqupdate", validator, newValue)
    }
  }

  const model = computed({
    get() {
      return validator.$model as string
    },
    set(newValue: string) {
      const value = newValue !== null ? newValue : ""
      updateValue(value)
    }
  })

  return { getTranslationKey, getTranslation, model }
}
