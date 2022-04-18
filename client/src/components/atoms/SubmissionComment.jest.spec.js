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

const styleCriteria = [
  {
    __typename: "StyleCriteria",
    id: 1,
    name: "Relevance",
    description: `Timely and responsive to an issue that concerns a specific public community.`,
    icon: "close_fullscreen",
  },
  {
    __typename: "StyleCriteria",
    id: 2,
    name: "Accessibility",
    description: `Connects with the public at large and resonates with specific, publicly engaged individuals and organizations.
       This usually requires unpacking technical terms, linking to source and related materials, providing transcripts
       for audio and video, and providing alt-text for images.`,
    icon: "accessibility",
  },
  {
    __typename: "StyleCriteria",
    id: 3,
    name: "Coherence",
    description: `Compelling and well-ordered according to the genre of the piece.`,
    icon: "psychology",
  },
  {
    __typename: "StyleCriteria",
    id: 4,
    name: "Scholarly Dialogue",
    description: `Cites and considers related discussions either within or outside of the academy, whether encountered in
        peer-reviewed literature or other media such as blogs, magazines, podcasts, galleries, or listservs.`,
    icon: "question_answer",
  },
]

installQuasarPlugin()
describe("SubmissionComment", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(SubmissionComment, {
        global: {
          mocks: {
            $t: (token) => token,
          },
          stubs: ["router-link"],
        },
        props: {
          submission: {
            publication: {
              style_criterias: styleCriteria,
            },
          },
          isInlineComment: true,
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

  it("reply button click triggers a reply", async () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper.findComponent({ ref: "comment_reply" }).exists()).toBe(false)
    const button = wrapper.findComponent({ ref: "reply_button" })
    await button.trigger("click")
    expect(wrapper.findComponent({ ref: "comment_reply" }).exists()).toBe(true)
  })
})
