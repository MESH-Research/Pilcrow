import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { Dialog } from "app/test/vitest/mockedPlugins"
import { beforeEach, describe, expect, it, test, vi } from "vitest"
import { ref } from "vue"
import CommentEditor from "./CommentEditor.vue"

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

installQuasarPlugin({ plugins: { Dialog } })
describe("CommentEditor", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(CommentEditor, {
        global: {
          provide: {
            submission: ref({
              publication: {
                style_criterias: styleCriteria,
              },
            }),
          },
          stubs: ["i18n-t"],
        },
        props: {
          commentType: "InlineComment",
          comment: {
            style_criteria: [],
          },
        },
      }),
    }
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  it("has no style criteria selected by default", async () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper.vm.hasStyleCriteria).toBe(false)
  })

  it("shows dialog if no criteria are selected", async () => {
    const { wrapper } = wrapperFactory()

    await flushPromises()
    await wrapper.findComponent({ name: "QForm" }).trigger("submit")
    expect(Dialog.dialog).toHaveBeenCalled()
  })

  it("does not show dialog if critiera are selected", async () => {
    const { wrapper } = wrapperFactory()
    await flushPromises()
    await wrapper
      .findAllComponents('[data-cy="criteria-toggle"]')
      .at(0)
      .trigger("click")
    expect(wrapper.vm.hasStyleCriteria).toBe(true)
    await wrapper.findComponent({ name: "QForm" }).trigger("submit")
    expect(Dialog.dialog).not.toHaveBeenCalled()
  })

  it("renders correct number of criteria controls", async () => {
    const { wrapper } = wrapperFactory()
    await flushPromises()
    expect(
      wrapper.findAllComponents('[data-cy="criteria-toggle"]').length,
    ).toBe(4)
  })

  it("emits cancel event on click", async () => {
    const { wrapper } = wrapperFactory()
    await flushPromises()
    const button = wrapper.findComponent({ ref: "cancel_button" })
    await button.trigger("click")
    expect(wrapper.emitted().cancel).toHaveLength(1)
  })
})
