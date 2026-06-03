import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it } from "vitest"
import type { LabsPreview } from "src/components/labs/LabsFeaturePreviews.vue"
import RecordOfReviewLabsPage from "./record-of-review.vue"

installQuasarPlugin()

// Stub the shared panel: this page is a thin wrapper, so we only assert it
// wires the right feature key + label and supplies a description slot.
const PanelStub = defineComponent({
  name: "LabsFeaturePanel",
  props: {
    featureKey: { type: String, default: "" },
    label: { type: String, default: "" }
  },
  setup:
    (_props, { slots }) =>
    () =>
      h("div", slots.default?.())
})

// Stub the previews component to capture the previews + previewsKey props
// without exercising its lightbox internals (covered in its own spec).
const PreviewsStub = defineComponent({
  name: "LabsFeaturePreviews",
  props: {
    previews: { type: Array, default: () => [] },
    previewsKey: { type: String, default: "" }
  },
  setup: () => () => h("div", { class: "previews-stub" })
})

function factory() {
  return mount(RecordOfReviewLabsPage, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { LabsFeaturePanel: PanelStub, LabsFeaturePreviews: PreviewsStub }
    }
  })
}

describe("Record of Review labs page", () => {
  it("wires the record_of_review feature key and label into the panel", () => {
    const panel = factory().findComponent(PanelStub)
    expect(panel.props("featureKey")).toBe("record_of_review")
    expect(panel.props("label")).toBe("labs.record_of_review.label")
  })

  it("renders the feature description in the panel slot", () => {
    expect(factory().text()).toContain("labs.record_of_review.description")
  })

  it("passes the record-of-review screenshot preview to the previews panel", () => {
    const previews = factory().findComponent(PreviewsStub)
    expect(previews.props("previewsKey")).toBe("labs.record_of_review.previews")
    expect(previews.props("previews")).toEqual([
      { key: "record", src: "/lab-features/record-of-review.png" },
      {
        key: "list",
        src: "/lab-features/record-of-review-list-light.png",
        srcDark: "/lab-features/record-of-review-list-dark.png"
      }
    ] satisfies LabsPreview[])
  })

  it("links to the Fider feedback board in a new tab", () => {
    const link = factory().find("[data-cy=ror_feedback_link]")
    expect(link.exists()).toBe(true)
    expect(link.attributes("href")).toBe(
      "https://feedback.pilcrow.dev/"
    )
    expect(link.attributes("target")).toBe("_blank")
    expect(link.attributes("rel")).toContain("noopener")
  })
})
