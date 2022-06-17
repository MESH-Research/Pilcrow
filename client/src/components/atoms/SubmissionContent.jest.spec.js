import SubmissionContent from "./SubmissionContent.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ref } from "vue"

installQuasarPlugin()

describe("SubmissionContent", () => {
  const makeTestArticle = () => {
    const submission = ref({
      content: {
        data: "Hello world",
      },
    })
    const activeComment = ref(null)
    return {
      submission,
      activeComment,
      wrapper: mount(SubmissionContent, {
        global: {
          mocks: {
            $t: (t) => t,
          },
          provide: {
            submission,
            activeComment,
          },
        },
      }),
    }
  }

  test("able to mount", () => {
    const { wrapper } = makeTestArticle()
    expect(wrapper).toBeTruthy()
  })
})
