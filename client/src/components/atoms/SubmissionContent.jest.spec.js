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
      inline_comments: [
        {
          __typename: "InlineComment",
          id: "1",
          content: "Sample Inline Comment 1",
          created_at: "2022-06-18T03:18:08.000000Z",
          created_by: {
            __typename: "User",
            email: "jestuser@ccrproject.dev",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          updated_at: "2022-06-18T03:18:08.000000Z",
          updated_by: {
            __typename: "User",
            email: "jestuser@ccrproject.dev",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          replies: [],
          style_criteria: [],
          from: 1,
          to: 2,
        },
        {
          __typename: "InlineComment",
          id: "2",
          content: "Sample Inline Comment 2",
          created_at: "2022-06-18T03:18:08.000000Z",
          created_by: {
            __typename: "User",
            email: "jestuser@ccrproject.dev",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          updated_at: "2022-06-18T03:18:08.000000Z",
          updated_by: {
            __typename: "User",
            email: "jestuser@ccrproject.dev",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          replies: [],
          style_criteria: [],
          from: 4,
          to: 5,
        },
        {
          __typename: "InlineComment",
          id: "3",
          content: "Sample Inline Comment 3",
          created_at: "2022-06-18T03:18:08.000000Z",
          created_by: {
            __typename: "User",
            email: "jestuser@ccrproject.dev",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          updated_at: "2022-06-18T03:18:08.000000Z",
          updated_by: {
            __typename: "User",
            email: "jestuser@ccrproject.dev",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          replies: [],
          style_criteria: [],
          from: 8,
          to: 10,
        },
      ],
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
      props: {
        highlightVisibility: true,
      },
    }
  }

  test("able to mount", () => {
    const { wrapper } = makeTestArticle()
    expect(wrapper).toBeTruthy()
  })

  test("inline comment widgets can be toggled", async () => {
    const { wrapper } = makeTestArticle()
    console.log(wrapper.vm.inlineComments)
    expect(wrapper.findAll("[data-cy='comment-widget']").length).toBe(3)
    await wrapper.setProps({ highlightVisibility: false })
    expect(wrapper.findAll("[data-cy='comment-widget']").length).toBe(0)
  })
})
