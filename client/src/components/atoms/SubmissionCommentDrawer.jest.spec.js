import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionCommentDrawer from "./SubmissionCommentDrawer.vue"
import { ref } from "vue"

installQuasarPlugin()
describe("SubmissionCommentDrawer", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(SubmissionCommentDrawer, {
        global: {
          provide: {
            submission: ref({
              id: "1",
              inline_comments: [
                {
                  id: "1",
                  content: "Hello World",
                },
              ],
            }),
            activeComment: ref(),
          },
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
})
