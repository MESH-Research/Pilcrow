/* eslint-env node */

/*
 * This file runs in a Node context (it's NOT transpiled by Babel), so use only
 * the ES6 features that are supported by your Node version. https://node.green/
 */

// Configuration for your app
// https://v2.quasar.dev/quasar-cli-vite/quasar-config-js

import { defineConfig } from "#q-app/wrappers"

export default defineConfig(() => {
  return {
    boot: ["i18n", "vue-apollo"],
    css: ["app.sass"],
    extras: [
      "mdi-v4",
      "fontawesome-v5",
      "roboto-font", // optional, you are not bound to it
      "material-icons", // optional, you are not bound to it
      "material-icons-outlined"
    ],
    framework: {
      config: { notify: { position: "top" }, dark: "auto" },
      iconSet: "material-icons", // Quasar icon set
      lang: "en-US", // Quasar language pack

      components: ["QList"],

      // Quasar plugins
      plugins: ["Cookies", "Dialog", "SessionStorage", "LocalStorage", "Notify"]
    },
    build: {
      target: {
        browser: ["es2019", "edge88", "firefox78", "chrome87", "safari13.1"],
        node: "node20"
      },
      extendViteConf(viteConf) {
        viteConf.experimental = viteConf.experimental || {}
        viteConf.experimental.renderBuiltUrl = function (
          filename,
          { hostType }
        ) {
          if (hostType === "js") {
            return { runtime: `window.__toCdnUrl(${JSON.stringify(filename)})` }
          } else {
            return { relative: true }
          }
        }
      },
      vueRouterMode: "history", // available values: 'hash', 'history'
      env: {
        VERSION: process.env.VERSION ?? undefined,
        VERSION_URL: process.env.VERSION_URL ?? undefined,
        VERSION_DATE: process.env.VERSION_DATE ?? undefined,
        APP_BANNER: process.env.APP_BANNER ?? undefined,
        APP_BANNER_CLASS: process.env.APP_BANNER_CLASS ?? undefined,
        APP_BANNER_LINK: process.env.APP_BANNER_LINK ?? undefined
      },
      vitePlugins: [
        [
          "vite-plugin-checker",
          {
            vueTsc: true,
            eslint: {
              lintCommand:
                'eslint -c ./eslint.config.js "./src*/**/*.{ts,js,mjs,cjs,vue}"',
              useFlatConfig: true
            }
          },
          { server: false }
        ]
      ],
      useFilenameHashes: false
    },
    devServer: {
      open: false,
      port: 8080,
      https: false,
      hmr: {
        clientPort: 443,
        path: "/__hmr"
      },
      allowedHosts: ["localhost", "pilcrow.lndo.site"]
    }
  }
})
