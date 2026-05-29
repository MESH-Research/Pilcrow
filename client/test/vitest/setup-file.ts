// This file will be run before each test file
import { config } from "@vue/test-utils"
import { vi } from "vitest"
config.global.mocks = {
  ...config.global.mocks,
  $t: (token) => token
}

vi.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
    t: (t) => t
  })
}))

// `definePage` is a compile-time macro from unplugin-vue-router that
// the Vite plugin transforms away before runtime. vitest.config.mjs
// doesn't include that plugin, so SFCs under src/routes/ that call
// `definePage(...)` fail with "definePage is not defined" at mount.
// Stubbing it as a global no-op lets those components mount under
// test without pulling the plugin into the vitest build.
vi.stubGlobal("definePage", () => {})
