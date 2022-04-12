import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import CommentEditor from "./CommentEditor.vue"
import flushPromises from "flush-promises"

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
    t: (t) => t,
  }),
}))

const mockDialog = jest.fn()
jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    dialog: mockDialog,
  }),
}))

installQuasarPlugin()
describe("CommentEditor", () => {
  const dialogReturn = {
    onOk: () => dialogReturn,
    onCancel: () => dialogReturn,
  }

  mockDialog.mockImplementation(() => dialogReturn)
  const wrapperFactory = () => {
    return {
      wrapper: mount(CommentEditor, {
        global: {
          mocks: {
            $t: (token) => token,
          },
        },
      }),
    }
  }

  beforeEach(() => {
    jest.clearAllMocks()
  })

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  it("has no style criteria selected by default", async () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper.vm.hasStyleCriteria).toBe(false)
  })

  it("shows dialog if no criteria are selected", async () => {
    const { wrapper } = wrapperFactory()
    await flushPromises()
    await wrapper.findComponent('[data-ref="submit"]').trigger("click")
    expect(mockDialog).toHaveBeenCalled()
  })

  test.each([
    ["relevance"],
    ["accessibility"],
    ["coherence"],
    ["scholarly_dialogue"],
  ])("recognizes if style criteria are selected", async (refAttr) => {
    const { wrapper } = wrapperFactory()
    await flushPromises()
    await wrapper.findComponent(`[data-ref="${refAttr}"]`).trigger("click")
    expect(wrapper.vm.hasStyleCriteria).toBe(true)
    expect(mockDialog).not.toHaveBeenCalled()
  })
})
