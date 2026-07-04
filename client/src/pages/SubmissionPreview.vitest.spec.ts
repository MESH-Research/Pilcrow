import { installApolloClient, installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { beforeEach, describe, expect, it, vi } from "vitest"
import SubmissionPreview from "./SubmissionPreview.vue"

vi.mock("vue-router", () => ({
  useRouter: () => ({ push: vi.fn() }),
  useRoute: () => ({ params: {} })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

const stubs = {
  "submission-preview-toolbar": true,
  "submission-content": true
}

describe("SubmissionPreview", () => {
  beforeEach(() => {
    mockClient.mockReset()
  })

  const mockSubmission = (overrides = {}) =>
    mockClient.getRequestHandler(GET_SUBMISSION_REVIEW).mockResolvedValue({
      data: {
        submission: {
          __typename: "Submission",
          id: "1",
          title: "A Submission",
          status: "UNDER_REVIEW",
          content: { data: "<p>Some content</p>" },
          publication: {
            __typename: "Publication",
            id: "1",
            style_criterias: [],
            editors: [],
            publication_admins: []
          },
          ...overrides
        }
      }
    })

  it("renders the localized preview banner when content is present", async () => {
    mockSubmission()
    const wrapper = mount(SubmissionPreview, {
      props: { id: "1" },
      global: { stubs }
    })
    await flushPromises()

    expect(wrapper.find(".q-banner").text()).toContain(
      "submission.preview_notice"
    )
  })
})
