import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { beforeEach, describe, expect, test, vi } from "vitest"
import { provide } from "vue"
import VQWrap from "./VQWrap.vue"

vi.mock("vue", async (importOriginal) => {
  const vue = await importOriginal()
  return {
    ...vue,
    provide: vi.fn()
  }
})

installQuasarPlugin()
describe("VQWrap", () => {
  const makeWrapper = (props) => {
    return mount(VQWrap, {
      props: {
        ...props
      }
    })
  }

  beforeEach(() => {
    vi.resetAllMocks()
  })
  test("able to mount", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("provides supplied props", () => {
    makeWrapper({ tPrefix: "testPrefix", formState: "idle" })
    expect(provide).toHaveBeenCalledWith(expect.anything(), "testPrefix")
  })

  test("emits update event", () => {
    const wrapper = makeWrapper()

    const vqupdate = provide.mock.calls.find(
      (call) => call[0] === "vqupdate"
    )[1]

    vqupdate("test", "test")

    expect(wrapper.emitted("vqupdate")[0]).toEqual(["test", "test"])
  })
})
