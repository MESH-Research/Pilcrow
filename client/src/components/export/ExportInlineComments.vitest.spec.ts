import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { describe, expect, test } from "vitest"
import ExportInlineComments from "./ExportInlineComments.vue"
import type { exportInlineCommentsFragment } from "src/graphql/generated/graphql"

installQuasarPlugin()

const mockUser = {
  display_label: "Test Author"
}

const mockReplyUser = {
  display_label: "Reply Author"
}

describe("ExportInlineComments", () => {
  const makeWrapper = (submission: exportInlineCommentsFragment) =>
    mount(ExportInlineComments, {
      props: { submission }
    })

  test("renders inline comments", () => {
    const wrapper = makeWrapper({
      inline_comments: [
        {
          id: "1",
          from: 0,
          to: 10,
          content: "<p>First inline comment</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          style_criteria: [],
          replies: []
        }
      ]
    })

    expect(wrapper.text()).toContain("Test Author")
    expect(wrapper.find(".comment-content").html()).toContain(
      "First inline comment"
    )
  })

  test("shows empty message when no comments", () => {
    const wrapper = makeWrapper({ inline_comments: [] })
    expect(wrapper.find(".text-caption").exists()).toBe(true)
  })

  test("renders replies", () => {
    const wrapper = makeWrapper({
      inline_comments: [
        {
          id: "1",
          from: 0,
          to: 10,
          content: "<p>Parent comment</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          style_criteria: [],
          replies: [
            {
              id: "2",
              content: "<p>A reply</p>",
              created_at: "2025-01-01T01:00:00Z",
              updated_at: "2025-01-01T01:00:00Z",
              created_by: mockReplyUser,
              reply_to_id: null
            }
          ]
        }
      ]
    })

    expect(wrapper.text()).toContain("Reply Author")
    expect(wrapper.findAll(".comment-content")).toHaveLength(2)
  })

  test("renders style criteria chips", () => {
    const wrapper = makeWrapper({
      inline_comments: [
        {
          id: "1",
          from: 0,
          to: 10,
          content: "<p>Comment with criteria</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          style_criteria: [{ name: "Clarity", icon: "visibility" }],
          replies: []
        }
      ]
    })

    expect(wrapper.text()).toContain("Clarity")
  })
})
