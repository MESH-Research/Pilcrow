import js from "@eslint/js"
import globals from "globals"
import pluginVue from "eslint-plugin-vue"
import pluginQuasar from "@quasar/app-vite/eslint"
import lodash from "lodash"
import tseslint from "typescript-eslint"
// the following is optional, if you want prettier too:
import prettierSkipFormatting from "@vue/eslint-config-prettier"
import pluginCypress from "eslint-plugin-cypress"
import vueParser from "vue-eslint-parser"
const { merge } = lodash
const config = [
  {
    ignores: ["src/graphql/generated/"]
  },
  ...pluginQuasar.configs.recommended(),
  ...pluginVue.configs["flat/recommended"],
  /**
   * https://eslint.vuejs.org
   *
   * pluginVue.configs.base
   *   -> Settings and rules to enable correct ESLint parsing.
   * pluginVue.configs[ 'flat/essential']
   *   -> base, plus rules to prevent errors or unintended behavior.
   * pluginVue.configs["flat/strongly-recommended"]
   *   -> Above, plus rules to considerably improve code readability and/or dev experience.
   * pluginVue.configs["flat/recommended"]
   *   -> Above, plus rules to enforce subjective community defaults to ensure consistency.
   */
  {
    files: ["src*/**/*.{vue,js,mjs,cjs,ts,mts}"],
    ignores: ["src*/**/*.vitest.spec.{js,mjs,cjs,ts,mts}"],
    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",

      globals: {
        ...globals.browser,
        ...globals.node, // SSR, Electron, config files
        process: "readonly", // process.env.*
        ga: "readonly", // Google Analytics
        cordova: "readonly",
        Capacitor: "readonly",
        chrome: "readonly", // BEX related
        browser: "readonly" // BEX related
      }
    },

    // add your custom rules here
    rules: {
      "prefer-promise-reject-errors": "off",
      "no-unused-vars": ["error", { caughtErrors: "none" }],
      // allow debugger during development only
      "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off"
    }
  },
  {
    files: ["src*/**/*.{ts,mts}"],
    ignores: ["src*/**/*.vitest.spec.{ts,mts}"],
    extends: [tseslint.configs.recommended],
    rules: {
      "no-unused-vars": "off",
      "@typescript-eslint/no-unused-vars": ["error", { caughtErrors: "none" }]
    }
  },
  {
    files: ["src*/**/*.vue"],
    extends: [tseslint.configs.recommended],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tseslint.parser
      }
    },
    rules: {
      "no-unused-vars": "off",
      "@typescript-eslint/no-unused-vars": ["error", { caughtErrors: "none" }],
      "vue/define-props-declaration": ["error", "type-based"]
    }
  },
  {
    files: [
      "test/vitest/**/*.{js,mjs,cjs,ts,mts,cts}",
      "src*/**/*.vitest.spec.{js,mjs,cjs,ts,mts}"
    ],
    ...js.configs.recommended,

    languageOptions: {
      globals: {
        ...globals.browser
      }
    }

    // vitest currently doesn't work well with non typescript projects
    // so we'll use the recommended config until something changes
    /*
    ...vitest.configs.recommended,
    settings: {
      vitest: {
        typecheck: false
      }
    },
    languageOptions: {
      globals: {
        ...vitest.environments.env.globals,
      },
    },
    */
  },
  {
    files: ["test/vitest/**/*.{ts,mts,cts}", "src*/**/*.vitest.spec.{ts,mts}"],
    extends: [tseslint.configs.recommended],
    rules: {
      "no-unused-vars": "off",
      "@typescript-eslint/no-unused-vars": ["error", { caughtErrors: "none" }]
    }
  },
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
  },
  {
    files: ["src-pwa/custom-service-worker.js"],
    languageOptions: {
      globals: {
        ...globals.serviceworker
      }
    }
  },

  prettierSkipFormatting // optional, if you want prettier
]
export default tseslint.config(...config)
