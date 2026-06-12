import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, vi } from "vitest"

// useDialogPluginComponent never shows the dialog standalone, so its teleported
// content never renders. Stub QDialog to a passthrough that renders the slot
// and mirrors hide → @hide so onDialogHide runs.
const QDialogStub = defineComponent({
  name: "QDialog",
  emits: ["hide"],
  setup(_, { slots, expose, emit }) {
    expose({ show: vi.fn(), hide: () => emit("hide") })
    return () => h("div", { class: "q-dialog-stub" }, slots.default?.())
  }
})

installQuasarPlugin()

import ReportAvatarDialog from "./ReportAvatarDialog.vue"

function factory() {
  return mount(ReportAvatarDialog, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { QDialog: QDialogStub }
    }
  })
}

describe("ReportAvatarDialog", () => {
  it("emits ok with a null reason when none is entered", async () => {
    const wrapper = factory()
    await wrapper.find('[data-cy="report_avatar_submit"]').trigger("click")

    expect(wrapper.emitted("ok")?.[0]).toEqual([{ reason: null }])
  })

  it("emits ok with the trimmed reason text", async () => {
    const wrapper = factory()
    await wrapper.find("textarea").setValue("  inappropriate  ")
    await wrapper.find('[data-cy="report_avatar_submit"]').trigger("click")

    expect(wrapper.emitted("ok")?.[0]).toEqual([{ reason: "inappropriate" }])
  })

  it("collapses a whitespace-only reason to null", async () => {
    const wrapper = factory()
    await wrapper.find("textarea").setValue("   ")
    await wrapper.find('[data-cy="report_avatar_submit"]').trigger("click")

    expect(wrapper.emitted("ok")?.[0]).toEqual([{ reason: null }])
  })

  it("hides without emitting ok when cancelled", async () => {
    const wrapper = factory()
    await wrapper.find('[data-cy="report_avatar_cancel"]').trigger("click")

    expect(wrapper.emitted("hide")).toBeTruthy()
    expect(wrapper.emitted("ok")).toBeFalsy()
  })
})
