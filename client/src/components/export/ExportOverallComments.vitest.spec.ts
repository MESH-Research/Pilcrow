import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, test } from "vitest"
import ExportOverallComments from "./ExportOverallComments.vue"
import type { exportOverallCommentsFragment } from "src/graphql/generated/graphql"

installQuasarPlugin()

const mockUser = {
  display_label: "Test Author"
}

const mockReplyUser = {
  display_label: "Reply Author"
}

describe("ExportOverallComments", () => {
  const makeWrapper = (submission: exportOverallCommentsFragment) =>
    mount(ExportOverallComments, {
      props: { submission }
    })

  test("renders overall comments", () => {
    const wrapper = makeWrapper({
      overall_comments: [
        {
          id: "1",
          content: "<p>First overall comment</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          replies: []
        }
      ]
    })

    expect(wrapper.text()).toContain("Test Author")
    expect(wrapper.find(".comment-content").html()).toContain(
      "First overall comment"
    )
  })

  test("shows empty message when no comments", () => {
    const wrapper = makeWrapper({ overall_comments: [] })
    expect(wrapper.find(".text-caption").exists()).toBe(true)
  })

  test("renders replies with author info", () => {
    const wrapper = makeWrapper({
      overall_comments: [
        {
          id: "1",
          content: "<p>Parent comment</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
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

  test("shows reply-to reference when reply targets another reply", () => {
    const wrapper = makeWrapper({
      overall_comments: [
        {
          id: "1",
          content: "<p>Parent</p>",
          created_at: "2025-01-01T00:00:00Z",
          updated_at: "2025-01-01T00:00:00Z",
          created_by: mockUser,
          replies: [
            {
              id: "2",
              content: "<p>First reply</p>",
              created_at: "2025-01-01T01:00:00Z",
              updated_at: "2025-01-01T01:00:00Z",
              created_by: mockReplyUser,
              reply_to_id: null
            },
            {
              id: "3",
              content: "<p>Reply to reply</p>",
              created_at: "2025-01-01T02:00:00Z",
              updated_at: "2025-01-01T02:00:00Z",
              created_by: mockUser,
              reply_to_id: "2"
            }
          ]
        }
      ]
    })

    expect(wrapper.find(".reply-reference").exists()).toBe(true)
  })
})
