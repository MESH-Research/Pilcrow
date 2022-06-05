import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import InlineComments from "./InlineComments.vue"
import { ref } from "vue"
import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"

installQuasarPlugin()
describe("InlineComments", () => {
  TimeAgo.addDefaultLocale(en)
  const wrapperFactory = () => {
    return {
      wrapper: mount(InlineComments, {
        global: {
          provide: {
            submission: ref({
              id: "1",
              inline_comments: [
                {
                  id: "1",
                  content: "Hello World",
                  created_at: "2022-06-05T01:57:20Z",
                  created_by: {
                    id: "1",
                    email: "sample@example.net",
                    name: "Sample Commenter",
                    username: "sampleCommenter",
                  },
                  replies: [],
                  style_criteria: [],
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

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })
})
