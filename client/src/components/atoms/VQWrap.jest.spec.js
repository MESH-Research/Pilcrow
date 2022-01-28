import VQWrap from "./VQWrap.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { provide } from "vue"

jest.mock("vue", () => ({
  ...jest.requireActual("vue"),
  provide: jest.fn(),
}))

installQuasarPlugin()
describe("VQWrap", () => {
  const makeWrapper = (props) => {
    return mount(VQWrap, {
      global: {
        mocks: {
          $t: (t) => t,
        },
      },
      props: {
        ...props,
      },
    })
  }

  beforeEach(() => {
    jest.resetAllMocks()
  })
  test("able to mount", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("provides supplied props", () => {
    makeWrapper({ tPrefix: "testPrefix", formState: "idle" })
    expect(provide).toBeCalledWith(expect.anything(), "testPrefix")
    const formState = provide.mock.calls.find((call) => call[0] === "formState")
    expect(formState[1]).toBeTruthy()
    expect(formState[1].value).toBe("idle")
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
