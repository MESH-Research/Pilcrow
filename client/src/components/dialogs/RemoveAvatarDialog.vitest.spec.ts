import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, vi } from "vitest"

// useDialogPluginComponent never shows the dialog when the component is
// mounted standalone, so its teleported content never renders. Stub QDialog to
// a passthrough that renders the default slot and exposes the show/hide methods
// onDialogOK/onDialogCancel call on dialogRef.
const QDialogStub = defineComponent({
  name: "QDialog",
  emits: ["hide"],
  setup(_, { slots, expose, emit }) {
    // onDialogCancel/onDialogOK call dialogRef.hide(); a real QDialog then
    // fires @hide. Mirror that so the component's onDialogHide runs.
    expose({ show: vi.fn(), hide: () => emit("hide") })
    return () => h("div", { class: "q-dialog-stub" }, slots.default?.())
  }
})

installQuasarPlugin()

import RemoveAvatarDialog from "./RemoveAvatarDialog.vue"

function factory() {
  return mount(RemoveAvatarDialog, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { QDialog: QDialogStub }
    }
  })
}

describe("RemoveAvatarDialog", () => {
  it("emits ok with blockFutureUploads false by default", async () => {
    const wrapper = factory()
    await wrapper
      .find('[data-cy="avatar_report_confirm_remove"]')
      .trigger("click")

    expect(wrapper.emitted("ok")?.[0]).toEqual([{ blockFutureUploads: false }])
  })

  it("emits ok with blockFutureUploads true once the checkbox is checked", async () => {
    const wrapper = factory()
    await wrapper
      .find('[data-cy="avatar_report_block_checkbox"]')
      .trigger("click")
    await wrapper
      .find('[data-cy="avatar_report_confirm_remove"]')
      .trigger("click")

    expect(wrapper.emitted("ok")?.[0]).toEqual([{ blockFutureUploads: true }])
  })

  it("hides without emitting ok when cancelled", async () => {
    const wrapper = factory()
    await wrapper.findAll("button")[0].trigger("click")

    expect(wrapper.emitted("hide")).toBeTruthy()
    expect(wrapper.emitted("ok")).toBeFalsy()
  })
})
