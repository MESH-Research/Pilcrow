// default src/boot/i18n.js content:

import { createI18n } from "vue-i18n"
import messages from "src/i18n"
// You'll need to create the src/i18n/index.js/.ts file too

export default ({ app }) => {
  // Create I18n instance
  const i18n = createI18n({
    locale: "en-US",
    legacy: false,
    globalInjection: true,
    messages,
  })

  // Tell app to use the I18n instance
  app.use(i18n)
}
