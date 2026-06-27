import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, beforeEach, afterEach, vi } from "vitest"
import { Notify } from "quasar"
import type { Mock } from "vitest"

vi.mock("vue-i18n", () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

// installQuasarPlugin doesn't register the Notify plugin; the failure path
// calls Notify.create, so swap it for a spy (keeping everything else real,
// including useDialogPluginComponent).
vi.mock("quasar", async (importOriginal) => {
  const actual = await importOriginal<typeof import("quasar")>()
  return { ...actual, Notify: { ...actual.Notify, create: vi.fn() } }
})

// useDialogPluginComponent never shows the dialog standalone; passthrough stub
// that renders the slot and mirrors hide → @hide.
const QDialogStub = defineComponent({
  name: "QDialog",
  emits: ["hide"],
  setup(_, { slots, expose, emit }) {
    expose({ show: vi.fn(), hide: () => emit("hide") })
    return () => h("div", { class: "q-dialog-stub" }, slots.default?.())
  }
})

// The component reads cropperRef.getResult().canvas; stub Cropper to expose a
// getResult whose return value each test controls via `cropResult`.
let cropResult: { canvas?: unknown } = {}
const CropperStub = defineComponent({
  name: "CropperStub",
  setup(_, { expose }) {
    expose({ getResult: () => cropResult })
    return () => h("div", { class: "cropper-stub" })
  }
})

installQuasarPlugin()

import AvatarCropDialog from "./AvatarCropDialog.vue"

// happy-dom's canvas can't produce a real 2d context / blob; swap createElement
// so the resize-and-encode path runs deterministically. Returns the mime type
// passed to toBlob so we can assert the output extension.
let lastToBlobType: string | undefined
function installCanvasStub() {
  const realCreate = document.createElement.bind(document)
  vi.spyOn(document, "createElement").mockImplementation((tag: string) => {
    if (tag !== "canvas") return realCreate(tag)
    return {
      width: 0,
      height: 0,
      getContext: () => ({ drawImage: vi.fn() }),
      toBlob: (cb: (b: Blob) => void, type: string) => {
        lastToBlobType = type
        cb(new Blob(["x"], { type }))
      }
    } as unknown as HTMLCanvasElement
  })
}

function factory(props: Record<string, unknown> = {}) {
  return mount(AvatarCropDialog, {
    props: { src: "data:image/png;base64,AAAA", ...props },
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { QDialog: QDialogStub, Cropper: CropperStub }
    }
  })
}

describe("AvatarCropDialog", () => {
  beforeEach(() => {
    cropResult = { canvas: {} }
    lastToBlobType = undefined
    ;(Notify.create as unknown as Mock).mockReset()
    installCanvasStub()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  it("emits ok with a PNG file when saving a cropped image", async () => {
    const wrapper = factory()
    await wrapper.find('[data-cy="avatar_crop_save"]').trigger("click")

    const payload = wrapper.emitted("ok")?.[0]?.[0] as { file: File }
    expect(payload.file).toBeInstanceOf(File)
    expect(payload.file.name).toBe("avatar.png")
    expect(payload.file.type).toBe("image/png")
    expect(lastToBlobType).toBe("image/png")
  })

  it("honours a JPEG mimeType, naming the file .jpg", async () => {
    const wrapper = factory({ mimeType: "image/jpeg" })
    await wrapper.find('[data-cy="avatar_crop_save"]').trigger("click")

    const payload = wrapper.emitted("ok")?.[0]?.[0] as { file: File }
    expect(payload.file.name).toBe("avatar.jpg")
    expect(payload.file.type).toBe("image/jpeg")
    expect(lastToBlobType).toBe("image/jpeg")
  })

  it("does not emit ok but notifies when the cropper yields no canvas", async () => {
    cropResult = {}
    const wrapper = factory()
    await wrapper.find('[data-cy="avatar_crop_save"]').trigger("click")

    expect(wrapper.emitted("ok")).toBeFalsy()
    expect(Notify.create as unknown as Mock).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
  })

  it("hides without emitting ok when cancelled", async () => {
    const wrapper = factory()
    await wrapper.find('[data-cy="avatar_crop_cancel"]').trigger("click")

    expect(wrapper.emitted("hide")).toBeTruthy()
    expect(wrapper.emitted("ok")).toBeFalsy()
  })
})
