import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionComment from "./SubmissionComment.vue"

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
    t: (t) => t,
  }),
}))
jest.mock("vue-router", () => ({
  useRouter: () => ({
    push: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("SubmissionComment", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(SubmissionComment, {
        global: {
          mocks: {
            $t: (token) => token,
          },
          stubs: ["router-link", "CommentEditor"],
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

  it("reply button click triggers a reply and cancel dismisses the inline comment editor", async () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper.findComponent({ ref: "comment_reply" }).exists()).toBe(false)
    const button = wrapper.findComponent({ ref: "reply_button" })
    await button.trigger("click")
    expect(wrapper.findComponent({ ref: "comment_reply" }).exists()).toBe(true)
    await wrapper.findComponent({ name: "CommentEditor" }).trigger("cancel")
    expect(wrapper.findComponent({ ref: "comment_reply" }).exists()).toBe(false)
  })
})
