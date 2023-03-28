// This file will be run before each test file
import { config } from '@vue/test-utils'
import { vi } from 'vitest'
config.global.mocks = {
  ...config.global.mocks,
  $t: (token) => token,
}

vi.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
    t: (t) => t,
  }),
}))