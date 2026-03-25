import { config } from "@vue/test-utils"
import { cloneDeep } from "lodash"
import { Quasar, type QuasarPluginOptions } from "quasar"
import { afterAll, beforeAll, vi } from "vitest"
import { ref } from "vue"

function qLayoutInjections() {
  return {
    _q_pc_: true,
    _q_l_: {
      header: { size: 0, offset: 0, space: false },
      right: { size: 300, offset: 0, space: false },
      footer: { size: 0, offset: 0, space: false },
      left: { size: 300, offset: 0, space: false },
      isContainer: ref(false),
      view: ref("lHh Lpr lff"),
      rows: ref({ top: "lHh", middle: "Lpr", bottom: "lff" }),
      height: ref(900),
      instances: {},
      update: vi.fn(),
      animate: vi.fn(),
      totalWidth: ref(1200),
      scroll: ref({ position: 0, direction: "up" }),
      scrollbarWidth: ref(125)
    }
  }
}

export function installQuasarPlugin(options?: Partial<QuasarPluginOptions>) {
  const globalConfigBackup = cloneDeep(config.global)

  beforeAll(() => {
    config.global.plugins.unshift([Quasar, options])
    config.global.provide = {
      ...config.global.provide,
      ...qLayoutInjections()
    }
  })

  afterAll(() => {
    config.global = globalConfigBackup
  })
}
