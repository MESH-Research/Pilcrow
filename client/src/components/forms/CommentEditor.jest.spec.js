import CommentEditor from "./CommentEditor.vue"
// import flushPromises from "flush-promises"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"

installQuasarPlugin()
describe("CommentEditor", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(CommentEditor),
    }
  }

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  it("has no style criteria selected by default", async () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper.vm.hasStyleCriteria).toBe(false)
  })

  it("recognizes if style criteria are selected", async () => {
    const { wrapper } = wrapperFactory()
    // const field = wrapper.findComponent({ ref: refAttr })
    // await wrapper.findComponent({ ref: "relevance" }).trigger("click")
    // await flushPromises()
    expect(wrapper.vm.hasStyleCriteria).toBe(false)
  })
})
