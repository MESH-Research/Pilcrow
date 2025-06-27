import { quasar, transformAssetUrls } from "@quasar/vite-plugin"
import vue from "@vitejs/plugin-vue"
import { resolve } from "path"
import tsconfigPaths from "vite-tsconfig-paths"
import { defineConfig } from "vitest/config"

// https://vitejs.dev/config/
export default defineConfig({
  test: {
    environment: "happy-dom",
    setupFiles: "test/vitest/setup-file.js",
    include: [
      // Matches vitest tests in any subfolder of 'src' or into 'test/vitest/__tests__'
      // Matches all files with extension 'js', 'jsx', 'ts' and 'tsx'
      "src/**/*.vitest.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}",
      "test/vitest/__tests__/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}"
    ]
  },
  plugins: [
    vue({
      template: { transformAssetUrls }
    }),
    quasar({
      sassVariables: "src/quasar-variables.scss"
    }),
    tsconfigPaths()
  ],
  resolve: {
    alias: {
      src: resolve(__dirname, "src"),
      app: resolve(__dirname, "./")
    }
  }
})
