import CommentEditor from "./CommentEditor.vue"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"

installQuasarPlugin()
describe("CommentEditor", () => {
  const wrapperFactory = (mocks = []) => {
    const mockClient = createMockClient()

    mocks?.forEach((mock) => {
      mockClient.setRequestHandler(...mock)
    })

    return {
      wrapper: mount(CommentEditor, {
        global: {
          mocks: {
            $t: (t) => t,
          },
        },
      }),
      mockClient,
    }
  }

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test.each([
    // ["relevance"],
    // ["accessibility"],
    // ["coherence"],
    ["scholarly_dialogue"],
  ])("recognizes if style criteria are selected", async (fieldRef) => {
    const { wrapper } = wrapperFactory()
    const field = wrapper.findComponent({ ref: fieldRef })
    console.log(wrapper)
    field.trigger("click")
    await flushPromises()
    expect(wrapper.vm.hasStyleCriteria).equals(true)
  })
})
