/* eslint-env node */
import js from "@eslint/js"
import globals from "globals"
import pluginVue from "eslint-plugin-vue"
import pluginQuasar from "@quasar/app-vite/eslint"
import {
  defineConfigWithVueTs,
  vueTsConfigs
} from "@vue/eslint-config-typescript"
import prettierSkipFormatting from "@vue/eslint-config-prettier"
const configs = [
  /**
   * Ignore quasar genereated files.
   */
  pluginQuasar.configs.recommended(),

  /**
   * Basic JS Rules
   * @see https://github.com/eslint/eslint/blob/main/packages/js/src/configs/eslint-recommended.js
   */
  js.configs.recommended,
  /**
   * @see https://eslint.vuejs.org/rules/#priority-c-recommended-potentially-dangerous-patterns
   */
  pluginVue.configs["flat/recommended"],
  /**
   * Wraps typescript rules to apply them to *.vue files
   *
   * @see https://github.com/typescript-eslint/typescript-eslint/blob/main/packages/eslint-plugin/src/configs/eslintrc/recommended-type-checked.ts
   */
  vueTsConfigs.recommendedTypeChecked,
  /**
   * Enable process global inside config files.
   */
  {
    files: ["eslint.config.js", "quasar.conf.ts"],
    languageOptions: {
      globals: {
        process: "readonly"
      }
    }
  },
  /**
   * Require type imports.
   */
  {
    files: ["**/*.ts", "**/*.vue"],
    rules: {
      "@typescript-eslint/consistent-type-imports": [
        "error",
        { prefer: "type-imports" }
      ],
      "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
      "@typescript-eslint/unbound-method": "off"
    }
  },
  /**
   * Setup globals for vue files.
   */
  {
    files: ["src*/**/*.{vue,js,ts,mjs,cjs}"],
    ignores: ["src*/**/*.vitest.spec.{js,mjs,cjs,ts}"],
    languageOptions: {
      ecmaVersion: "latest",
      sourceType: "module",

      globals: {
        ...globals.browser,
        process: "readonly", // process.env.*
        ga: "readonly", // Google Analytics
        cordova: "readonly",
        Capacitor: "readonly",
        chrome: "readonly", // BEX related
        browser: "readonly" // BEX related
      }
    }
  },
  /**
   * Disable multi-word component name rule in pages folder.
   */
  {
    files: ["src/pages/**/*.vue"],
    rules: {
      "vue/multi-word-component-names": "off"
    }
  },
  /**
   * Enable browser globals in vitest files.
   */
  {
    files: [
      "test/vitest/**/*.{js,mjs,cjs,ts,mts,cts}",
      "src*/**/*.vitest.spec.{js,mjs,cjs}"
    ],

    languageOptions: {
      globals: {
        ...globals.browser
      }
    }
  },
  prettierSkipFormatting // optional, if you want prettier
]

export default defineConfigWithVueTs(...configs)
