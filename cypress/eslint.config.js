import js from "@eslint/js"
import globals from "globals"
import pluginCypress from "eslint-plugin-cypress"
import lodash from "lodash"
const { merge } = lodash
const config = [
  {
    files: ["test/cypress/**/*.{js,mjs,cjs}"],
    ...merge(pluginCypress.configs.recommended, {
      ignores: ["test/cypress/support/**/*.{js,mjs,cjs}"],
      languageOptions: {
        sourceType: "commonjs",
        globals: {
          ...globals.node
        }
      }
    })
  },
  {
    files: ["test/cypress/support/**/*.{js,mjs,cjs}"],
    ...merge(pluginCypress.configs.recommended, {
      languageOptions: {
        sourceType: "module"
      }
    })
  }
]
export default config
