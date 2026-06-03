import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { describe, expect, it, vi } from "vitest"
import LabsFeaturePreviews from "./LabsFeaturePreviews.vue"

// Echo i18n keys so assertions read against the resolved key strings.
vi.mock("vue-i18n", () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

installQuasarPlugin()

const previews = [
  { key: "record", src: "/lab-features/record-of-review.png" },
  { key: "second", src: "/lab-features/second.png" }
]

function factory() {
  return mount(LabsFeaturePreviews, {
    props: { previews, previewsKey: "labs.demo.previews" },
    global: { mocks: { $t: (key: string) => key } },
    // q-dialog portals its content; attach so the lightbox is queryable.
    attachTo: document.body
  })
}

describe("LabsFeaturePreviews", () => {
  it("renders a thumbnail card per preview with its title and caption", () => {
    const wrapper = factory()
    expect(wrapper.find('[data-cy="labs_preview_record"]').exists()).toBe(true)
    expect(wrapper.find('[data-cy="labs_preview_second"]').exists()).toBe(true)
    expect(wrapper.text()).toContain("labs.demo.previews.record.title")
    expect(wrapper.text()).toContain("labs.demo.previews.record.caption")
    wrapper.unmount()
  })

  it("keeps the lightbox closed until a preview is activated", () => {
    const wrapper = factory()
    expect(
      document.querySelector('[data-cy="labs_preview_lightbox"]')
    ).toBeNull()
    wrapper.unmount()
  })

  it("opens the lightbox with the clicked preview's image and title", async () => {
    const wrapper = factory()
    await wrapper.find('[data-cy="labs_preview_record"]').trigger("click")
    await flushPromises()

    const img = document.querySelector<HTMLImageElement>(
      ".labs-feature-lightbox-img"
    )
    expect(img).not.toBeNull()
    expect(img?.getAttribute("src")).toBe("/lab-features/record-of-review.png")
    expect(document.body.textContent).toContain(
      "labs.demo.previews.record.title"
    )
    wrapper.unmount()
  })
})
