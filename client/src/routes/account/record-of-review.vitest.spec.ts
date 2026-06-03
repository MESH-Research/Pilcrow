import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { beforeEach, describe, expect, it, vi } from "vitest"
import RecordOfReviewPage from "./record-of-review.vue"

vi.mock("vue-router", () => ({
  useRoute: vi.fn(() => ({ query: {} })),
  useRouter: vi.fn(() => ({ push: vi.fn(), replace: vi.fn() }))
}))

// Capture the export builders so we can assert which download path ran
// without exercising html2canvas / zip internals (covered in their own spec).
// vi.hoisted lifts the mocks above the hoisted vi.mock factory.
const { buildRorExportHtml, buildRorExportBlob, buildRorZipBlob } = vi.hoisted(
  () => ({
    buildRorExportHtml: vi.fn(async () => "<html></html>"),
    buildRorExportBlob: vi.fn(() => new Blob(["html"], { type: "text/html" })),
    buildRorZipBlob: vi.fn(async () => new Blob(["zip"]))
  })
)
vi.mock("src/utils/recordOfReviewExport", () => ({
  buildRorExportHtml,
  buildRorExportBlob,
  buildRorZipBlob
}))

installQuasarPlugin()
installApolloClient()

// Table stub: lets the test drive the v-model:selected the page reacts to.
const TableStub = defineComponent({
  name: "RecordOfReviewTable",
  props: {
    query: { type: Object, default: null },
    selected: { type: Array, default: () => [] }
  },
  emits: ["update:selected"],
  setup: () => () => h("div", { class: "table-stub" })
})

// Record stub: exposes getRecordElement like the real component so the page's
// recordRefs collect a (possibly null) element for confirmDownload.
function makeRecordStub(element: HTMLElement | null) {
  return defineComponent({
    name: "RecordOfReview",
    props: { assignment: { type: Object, required: true } },
    setup: (_props, { expose }) => {
      expose({ getRecordElement: () => element })
      return () => h("div", { class: "record-stub" })
    }
  })
}

const assignment = (id: string, title: string) => ({
  id,
  submission: { title }
})

describe("Record of Review page", () => {
  beforeEach(() => {
    vi.clearAllMocks()
    // jsdom lacks object-URL APIs used by triggerDownload.
    window.URL.createObjectURL = vi.fn(() => "blob:mock")
    window.URL.revokeObjectURL = vi.fn()
  })

  const makeWrapper = (
    recordEl: HTMLElement | null = document.createElement("div")
  ) =>
    mount(RecordOfReviewPage, {
      attachTo: document.body,
      global: {
        stubs: {
          "router-link": true,
          "i18n-t": true,
          RecordOfReviewTable: TableStub,
          RecordOfReview: makeRecordStub(recordEl)
        }
      }
    })

  const select = async (
    wrapper: ReturnType<typeof makeWrapper>,
    rows: ReturnType<typeof assignment>[]
  ) => {
    wrapper.findComponent(TableStub).vm.$emit("update:selected", rows)
    await flushPromises()
  }

  it("mounts without errors", async () => {
    const wrapper = makeWrapper()
    await flushPromises()
    expect(wrapper.find("[data-cy=record_of_review]").exists()).toBe(true)
  })

  it("hides the download-all banner with one or fewer selections", async () => {
    const wrapper = makeWrapper()
    await select(wrapper, [assignment("1", "One")])
    expect(wrapper.findComponent({ name: "QBanner" }).exists()).toBe(false)
  })

  it("shows the download-all banner once more than one record is selected", async () => {
    const wrapper = makeWrapper()
    await select(wrapper, [assignment("1", "One"), assignment("2", "Two")])
    expect(wrapper.findAll(".record-stub")).toHaveLength(2)
    expect(wrapper.findComponent({ name: "QBanner" }).exists()).toBe(true)
  })

  const openDialog = async (wrapper: ReturnType<typeof makeWrapper>) => {
    // The banner's first button is the "download all" action that opens the dialog.
    await wrapper.findAllComponents({ name: "QBtn" })[0].trigger("click")
    await flushPromises()
  }

  const confirm = async (wrapper: ReturnType<typeof makeWrapper>) => {
    const btn = wrapper
      .findAllComponents({ name: "QBtn" })
      .find((b) => b.attributes("data-cy") === "ror_download_confirm")
    await btn!.trigger("click")
    await flushPromises()
  }

  it("downloads a combined HTML file by default", async () => {
    const wrapper = makeWrapper()
    await select(wrapper, [assignment("1", "One"), assignment("2", "Two")])
    await openDialog(wrapper)
    await confirm(wrapper)
    expect(buildRorExportHtml).toHaveBeenCalledTimes(1)
    expect(buildRorExportBlob).toHaveBeenCalledTimes(1)
    expect(buildRorZipBlob).not.toHaveBeenCalled()
    expect(window.URL.createObjectURL).toHaveBeenCalled()
  })

  it("downloads a zip when the zip format is selected", async () => {
    const wrapper = makeWrapper()
    await select(wrapper, [assignment("1", "One"), assignment("2", "Two")])
    await openDialog(wrapper)
    wrapper
      .findComponent({ name: "QOptionGroup" })
      .vm.$emit("update:modelValue", "zip")
    await flushPromises()
    await confirm(wrapper)
    expect(buildRorZipBlob).toHaveBeenCalledTimes(1)
    expect(buildRorExportHtml).not.toHaveBeenCalled()
  })

  it("does nothing when no record elements are available", async () => {
    const wrapper = makeWrapper(null)
    await select(wrapper, [assignment("1", "One"), assignment("2", "Two")])
    await openDialog(wrapper)
    await confirm(wrapper)
    expect(buildRorExportHtml).not.toHaveBeenCalled()
    expect(buildRorZipBlob).not.toHaveBeenCalled()
  })
})
