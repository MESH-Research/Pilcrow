// default src/boot/i18n.js content:

import { type ComposerTranslation, createI18n } from "vue-i18n"
import messages from "src/i18n"
// You'll need to create the src/i18n/index.js/.ts file too

export default ({ app }) => {
  // Create I18n instance
  const i18n = createI18n({
    locale: "en-US",
    legacy: false,
    globalInjection: true,
    messages
  })

  // Tell app to use the I18n instance
  app.use(i18n)
}

declare module "@vue/runtime-core" {
  interface ComponentCustomProperties {
    /**
     * @deprecated Official way is to use `const { t } = useI18n()` as described in https://vue-i18n.intlify.dev/guide/migration/vue3#migration-to-composition-api-from-legacy-api
     */
    $t: ComposerTranslation
  }
}
