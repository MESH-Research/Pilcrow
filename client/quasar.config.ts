/* eslint-env node */

/*
 * This file runs in a Node context (it's NOT transpiled by Babel), so use only
 * the ES6 features that are supported by your Node version. https://node.green/
 */

// Configuration for your app
// https://v2.quasar.dev/quasar-cli-vite/quasar-config-js

import { resolve } from "node:path"
import { defineConfig } from "#q-app/wrappers"

export default defineConfig(function (/* ctx */) {
  return {
    eslint: {
      // fix: true,
      // include = [],
      // exclude = [],
      // rawOptions = {},
      warnings: true,
      errors: true
    },

    // https://v2.quasar.dev/quasar-cli/prefetch-feature
    // preFetch: true,

    // app boot file (/src/boot)
    // --> boot files are part of "main.js"
    // https://v2.quasar.dev/quasar-cli/boot-files
    boot: ["i18n", "vue-apollo"],

    // https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#css
    css: ["app.sass"],

    // https://github.com/quasarframework/quasar/tree/dev/extras
    extras: [
      // 'ionicons-v4',
      "mdi-v4",
      "fontawesome-v5",
      // 'eva-icons',
      // 'themify',
      // 'line-awesome',
      // 'roboto-font-latin-ext', // this or either 'roboto-font', NEVER both!

      "roboto-font", // optional, you are not bound to it
      "material-icons", // optional, you are not bound to it
      "material-icons-outlined"
    ],

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#build
    build: {
      typescript: {
        strict: false,
        vueShim: true,
      },

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
        viteConf.plugins = viteConf.plugins || []
        viteConf.plugins.push({
          name: "watch-backend-schema",
          configureServer(server) {
            const backendGraphqlDir = resolve(__dirname, "../backend/graphql")
            let debounceTimer: ReturnType<typeof setTimeout> | null = null
            server.watcher.add(backendGraphqlDir)
            server.watcher.on("change", (filePath) => {
              if (!filePath.startsWith(backendGraphqlDir) || !filePath.endsWith(".graphql")) return
              if (debounceTimer) clearTimeout(debounceTimer)
              debounceTimer = setTimeout(async () => {
                try {
                  const { generate, loadContext } = await import("@graphql-codegen/cli")
                  const ctx = await loadContext()
                  await generate({ ...ctx.getConfig(), watch: false })
                  console.log("[watch-backend-schema] Types regenerated after schema change")
                } catch (e) {
                  console.warn("[watch-backend-schema] Codegen failed:", (e as Error).message)
                }
              }, 500)
            })
          },
        })
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
      // vueRouterBase,
      // vueDevtools,
      // vueOptionsAPI: false,

      // rebuildCache: true, // rebuilds Vite/linter/etc cache on startup

      // publicPath: '/',
      // analyze: true,
      // rawDefine: {}
      // ignorePublicFolder: true,
      // minify: false,
      // polyfillModulePreload: true,
      // distDir

      // extendViteConf (viteConf) {},
      // viteVuePluginOptions: {},

      // vitePlugins: [
      //   [ 'package-name', { ..options.. } ]
      // ]
      vitePlugins: [
        [
          "vite-plugin-checker",
          {
            vueTsc: true,
            eslint: {
              lintCommand:
                'eslint -c ./eslint.config.js "./src*/**/*.{js,mjs,cjs,ts,mts,vue}"',
              useFlatConfig: true
            }
          },
          { server: false }
        ],
        [
          "vite-plugin-graphql-codegen",
          {
            runOnStart: true,
            runOnBuild: true,
            throwOnStart: false,
            matchOnDocuments: true,
            matchOnSchemas: false,
            configOverrideOnBuild: {
              schema: "src/graphql/schema.graphql",
            },
          },
        ],
      ],
      useFilenameHashes: false
    },

    // Full list of options: https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#devServer
    devServer: {
      open: false,
      port: 8080,
      https: false,
      hmr: {
        clientPort: 443,
        path: "/__hmr"
      },
      allowedHosts: ["localhost", "pilcrow.lndo.site"]
    },

    // https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#framework
    framework: {
      config: { notify: { position: "top" }, dark: "auto" },
      iconSet: "material-icons", // Quasar icon set
      lang: "en-US", // Quasar language pack

      components: ["QList"],
      // directives: [],

      // Quasar plugins
      plugins: ["Cookies", "Dialog", "SessionStorage", "LocalStorage", "Notify"]
    },

    // animations: 'all', // --- includes all animations
    // https://v2.quasar.dev/options/animations
    animations: [],

    // https://v2.quasar.dev/quasar-cli-vite/quasar-config-js#property-sourcefiles
    // sourceFiles: {
    //   rootComponent: 'src/App.vue',
    //   router: 'src/router/index',
    //   store: 'src/store/index',
    //   registerServiceWorker: 'src-pwa/register-service-worker',
    //   serviceWorker: 'src-pwa/custom-service-worker',
    //   pwaManifestFile: 'src-pwa/manifest.json',
    //   electronMain: 'src-electron/electron-main',
    //   electronPreload: 'src-electron/electron-preload'
    // },

    // https://v2.quasar.dev/quasar-cli/developing-ssr/configuring-ssr
    ssr: {
      // ssrPwaHtmlFilename: 'offline.html', // do NOT use index.html as name!
      // will mess up SSR

      // extendSSRWebserverConf (esbuildConf) {},
      // extendPackageJson (json) {},

      pwa: false,

      // manualStoreHydration: true,
      // manualPostHydrationTrigger: true,

      prodPort: 3000, // The default port that the production server should use
      // (gets superseded if process.env.PORT is specified at runtime)

      middlewares: [
        "render" // keep this as last one
      ]
    },
  }
})
