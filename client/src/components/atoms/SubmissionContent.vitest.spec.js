import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { ref } from "vue"
import SubmissionContent from "./SubmissionContent.vue"

import { describe, expect, test } from "vitest"

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
            email: "jestuser@meshresearch.net",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          updated_at: "2022-06-18T03:18:08.000000Z",
          updated_by: {
            __typename: "User",
            email: "jestuser@meshresearch.net",
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
            email: "jestuser@meshresearch.net",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          updated_at: "2022-06-18T03:18:08.000000Z",
          updated_by: {
            __typename: "User",
            email: "jestuser@meshresearch.net",
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
            email: "jestuser@meshresearch.net",
            id: "1",
            name: "Jest User",
            username: "jestUser",
          },
          updated_at: "2022-06-18T03:18:08.000000Z",
          updated_by: {
            __typename: "User",
            email: "jestuser@meshresearch.net",
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
          provide: {
            submission,
            activeComment,
            commentDrawerOpen: true,
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

  test("inline comment annotations can be toggled", async () => {
    const { wrapper } = makeTestArticle()
    expect(wrapper.vm.annotations.length).toBe(3)
    await wrapper.setProps({ highlightVisibility: false })
    expect(wrapper.vm.annotations.length).toBe(0)
    await wrapper.setProps({ highlightVisibility: true })
    expect(wrapper.vm.annotations.length).toBe(3)
  })

  test("font size of submission content can be changed", async () => {
    const { wrapper } = makeTestArticle()
    expect(wrapper.vm.fontSize).toBe(1)
    await wrapper.find("[data-cy=increase_font]").trigger("click")
    expect(wrapper.vm.fontSize).toBe(1.05)
    await wrapper.find("[data-cy=decrease_font]").trigger("click")
    expect(wrapper.vm.fontSize).toBe(1)
    expect(wrapper.find("[data-cy=decrease_font]").classes("disabled")).toBe(
      true
    )
  })
})
