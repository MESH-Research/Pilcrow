import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import {
  GetExportOptionsDocument,
  type GetExportOptionsQuery
} from "src/graphql/generated/graphql"
import { beforeEach, describe, expect, test, vi } from "vitest"
import SubmissionExportPage from "./SubmissionExport.vue"

vi.mock("vue-router", () => ({
  useRoute: vi.fn(() => ({
    query: {}
  })),
  useRouter: vi.fn(() => ({
    push: vi.fn(),
    replace: vi.fn()
  }))
}))

installQuasarPlugin()
const mockClient = installApolloClient()

const mockExportOptionsResponse: { data: GetExportOptionsQuery } = {
  data: {
    submission: {
      __typename: "Submission",
      id: "1",
      title: "Test Submission",
      inline_comments: [
        { id: "10", replies: [{ id: "11" }] },
        { id: "12", replies: [] }
      ],
      overall_comments: [{ id: "20", replies: [{ id: "21" }, { id: "22" }] }],
      commenters: [
        {
          id: "100",
          display_label: "Test User",
          email: "test@example.com"
        }
      ]
    }
  }
}

describe("SubmissionExport page", () => {
  beforeEach(() => {
    mockClient.mockReset()
  })

  const makeWrapper = () =>
    mount(SubmissionExportPage, {
      props: { id: "1" }
    })

  test("component mounts and queries submission", async () => {
    const handler = mockClient
      .getRequestHandler(GetExportOptionsDocument)
      .mockResolvedValue(mockExportOptionsResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    expect(handler).toHaveBeenCalled()
    expect(wrapper.text()).toContain("Test Submission")
  })

  test("shows loading state before data arrives", () => {
    mockClient
      .getRequestHandler(GetExportOptionsDocument)
      .mockResolvedValue(mockExportOptionsResponse)

    const wrapper = makeWrapper()
    expect(wrapper.text()).toContain("loading")
  })

  test("displays comment count options", async () => {
    mockClient
      .getRequestHandler(GetExportOptionsDocument)
      .mockResolvedValue(mockExportOptionsResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    const checkbox = wrapper.find(".q-checkbox")
    expect(checkbox.exists()).toBe(true)
  })

  test("shows download and preview buttons", async () => {
    mockClient
      .getRequestHandler(GetExportOptionsDocument)
      .mockResolvedValue(mockExportOptionsResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    const buttons = wrapper.findAll(".q-btn")
    expect(buttons.length).toBeGreaterThanOrEqual(2)
  })
})
