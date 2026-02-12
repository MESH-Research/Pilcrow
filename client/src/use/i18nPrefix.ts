import { toValue, type MaybeRefOrGetter } from "vue"
import { useI18n } from "vue-i18n"
import type { ComposerTranslation } from "vue-i18n"

export function useI18nPrefix(prefix: MaybeRefOrGetter<string>) {
  const { t, te } = useI18n()
  const prefixKey = (key: string) => `${toValue(prefix)}.${key}`

  const pt = ((key: string, ...args: unknown[]) =>
    (t as Function)(prefixKey(key), ...args)) as ComposerTranslation

  const pte = (key: string) => te(prefixKey(key))

  return { pt, pte, t, te }
}
