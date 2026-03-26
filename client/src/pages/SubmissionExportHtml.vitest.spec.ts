import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import {
  GetSubmissionExportDataDocument,
  type GetSubmissionExportDataQuery
} from "src/graphql/generated/graphql"
import { beforeEach, describe, expect, test, vi } from "vitest"
import SubmissionExportHtmlPage from "./SubmissionExportHtml.vue"

vi.mock("vue-router", () => ({
  useRoute: vi.fn(() => ({
    query: { comments: "ALL" }
  })),
  useRouter: vi.fn(() => ({
    push: vi.fn(),
    replace: vi.fn()
  }))
}))

installQuasarPlugin()
const mockClient = installApolloClient()

const mockUser = {
  __typename: "User" as const,
  display_label: "Test Author"
}

const mockExportDataResponse: { data: GetSubmissionExportDataQuery } = {
  data: {
    submission: {
      __typename: "Submission",
      id: "1",
      title: "Test Submission",
      content: { data: "<p>Test content</p>" },
      inline_comments: [
        {
          id: "10",
          from: 0,
          to: 5,
          content: "<p>Inline comment</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          style_criteria: [],
          replies: []
        }
      ],
      overall_comments: [
        {
          id: "20",
          content: "<p>Overall comment</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          replies: []
        }
      ]
    }
  }
}

// Stub child components that depend on deep fragment data
const stubs = {
  SubmissionExportGenerator: {
    template: "<div data-stub='export-generator' />"
  }
}

describe("SubmissionExportHtml page", () => {
  beforeEach(() => {
    mockClient.mockReset()
  })

  const makeWrapper = () =>
    mount(SubmissionExportHtmlPage, {
      props: { id: "1" },
      global: { stubs }
    })

  test("component mounts and queries submission data", async () => {
    const handler = mockClient
      .getRequestHandler(GetSubmissionExportDataDocument)
      .mockResolvedValue(mockExportDataResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    expect(handler).toHaveBeenCalled()
    expect(wrapper.find("h2").text()).toContain("export.preview")
  })

  test("shows loading state before data arrives", () => {
    mockClient
      .getRequestHandler(GetSubmissionExportDataDocument)
      .mockResolvedValue(mockExportDataResponse)

    const wrapper = makeWrapper()
    expect(wrapper.text()).toContain("loading")
  })

  test("renders download button when data loads", async () => {
    mockClient
      .getRequestHandler(GetSubmissionExportDataDocument)
      .mockResolvedValue(mockExportDataResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    const buttons = wrapper.findAll(".q-btn")
    expect(buttons.length).toBeGreaterThanOrEqual(1)
  })

  test("renders preview iframe when data loads", async () => {
    mockClient
      .getRequestHandler(GetSubmissionExportDataDocument)
      .mockResolvedValue(mockExportDataResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    expect(wrapper.find("iframe").exists()).toBe(true)
  })

  test("passes submission to export generator stub", async () => {
    mockClient
      .getRequestHandler(GetSubmissionExportDataDocument)
      .mockResolvedValue(mockExportDataResponse)

    const wrapper = makeWrapper()
    await flushPromises()

    expect(wrapper.find("[data-stub='export-generator']").exists()).toBe(true)
  })
})
