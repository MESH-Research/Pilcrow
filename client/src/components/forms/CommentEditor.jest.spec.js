// import flushPromises from "flush-promises"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import CommentEditor from "./CommentEditor.vue"

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

  test.each([
    ["relevance"],
    // ["accessibility"],
    // ["coherence"],
    // ["scholarly_dialogue"],
  ])("recognizes if style criteria are selected", async (refAttr) => {
    const { wrapper } = wrapperFactory()
    console.log(wrapper)
    await wrapper.findComponent({ ref: refAttr }).trigger("click")
    expect(wrapper.vm.hasStyleCriteria).toBe(false)
  })
})
