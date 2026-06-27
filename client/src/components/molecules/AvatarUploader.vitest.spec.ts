import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import { Dialog, Notify } from "quasar"
import type { Mock } from "vitest"
import { UPLOAD_USER_AVATAR, DELETE_USER_AVATAR } from "src/graphql/mutations"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

vi.mock("vue-i18n", () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

// Dialog.create / Notify.create attach to the Quasar singletons only at
// app-install time (inside mount), too late for assertions; swap in vi.fn()s.
vi.mock("quasar", async (importOriginal) => {
  const actual = await importOriginal<typeof import("quasar")>()
  return {
    ...actual,
    Dialog: { ...actual.Dialog, create: vi.fn() },
    Notify: { ...actual.Notify, create: vi.fn() }
  }
})

const dialogCreate = Dialog.create as unknown as Mock
const notifyCreate = Notify.create as unknown as Mock

// happy-dom's FileReader resolves onload on a real timer, which outruns
// flushPromises and bleeds the callback into later tests. Swap in a
// synchronous reader so the data-URL → cropper path runs within the change
// handler's await.
class SyncFileReader {
  result = "data:image/png;base64,AAAA"
  onload: (() => void) | null = null
  readAsDataURL() {
    this.onload?.()
  }
}
vi.stubGlobal("FileReader", SyncFileReader)

// AvatarCropDialog returns the cropped File via onOk; mock the dialog to
// resolve with a fixed file so the upload path runs without a real cropper.
const CROPPED_FILE = new File(["x"], "cropped.png", { type: "image/png" })
function mockCropOk(file: File = CROPPED_FILE) {
  dialogCreate.mockImplementation(() => ({
    onOk: (cb: (payload: { file: File }) => void) => {
      cb({ file })
      return { onCancel: () => ({ onDismiss: () => ({}) }) }
    }
  }))
}

// AvatarImage runs identicon/image logic irrelevant here; stub it out.
const AvatarImageStub = defineComponent({
  name: "AvatarImage",
  props: { user: Object, variant: String, rounded: Boolean },
  setup: () => () => null
})

installQuasarPlugin()
const mockClient = installApolloClient()

import AvatarUploader from "./AvatarUploader.vue"

const BASE_USER: avatarImageFragment & { avatar_upload_blocked?: boolean } = {
  __typename: "User",
  id: "1",
  email: "user@example.com",
  staged: null,
  avatar: null
}

function factory(user: Partial<typeof BASE_USER> = {}) {
  return mount(AvatarUploader, {
    props: { user: { ...BASE_USER, ...user } },
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { AvatarImage: AvatarImageStub }
    }
  })
}

function pngFile(size = 10) {
  return new File([new Uint8Array(size)], "a.png", { type: "image/png" })
}

// Drive the hidden <input type=file> change handler with a chosen file.
async function selectFile(wrapper: ReturnType<typeof factory>, file: File) {
  const input = wrapper.find('[data-cy="avatar_file_input"]')
    .element as HTMLInputElement
  Object.defineProperty(input, "files", { value: [file], configurable: true })
  await wrapper.find('[data-cy="avatar_file_input"]').trigger("change")
  await flushPromises()
}

describe("AvatarUploader", () => {
  beforeEach(() => {
    mockClient.mockReset()
    dialogCreate.mockReset()
    notifyCreate.mockReset()
    mockClient.getRequestHandler(UPLOAD_USER_AVATAR).mockResolvedValue({
      data: { uploadUserAvatar: { __typename: "User", id: "1", avatar: null } }
    })
    mockClient.getRequestHandler(DELETE_USER_AVATAR).mockResolvedValue({
      data: { deleteUserAvatar: { __typename: "User", id: "1", avatar: null } }
    })
  })

  it("renders nothing when uploads are blocked", () => {
    const wrapper = factory({ avatar_upload_blocked: true })
    expect(wrapper.find(".avatar-uploader").exists()).toBe(false)
  })

  it("shows the upload label and no remove button when there is no avatar", () => {
    const wrapper = factory()
    expect(wrapper.find('[data-cy="avatar_upload_button"]').text()).toContain(
      "account.avatar.upload_button"
    )
    expect(wrapper.find('[data-cy="avatar_remove_button"]').exists()).toBe(
      false
    )
  })

  it("shows the change label and a remove button when an avatar exists", () => {
    const wrapper = factory({
      avatar: {
        __typename: "Avatar",
        thumb_url: "t",
        medium_url: "m",
        url: "u"
      }
    })
    expect(wrapper.find('[data-cy="avatar_upload_button"]').text()).toContain(
      "account.avatar.change_button"
    )
    expect(wrapper.find('[data-cy="avatar_remove_button"]').exists()).toBe(true)
  })

  it("rejects an unsupported file type without opening the cropper", async () => {
    const wrapper = factory()
    await selectFile(wrapper, new File(["x"], "a.gif", { type: "image/gif" }))

    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
    expect(dialogCreate).not.toHaveBeenCalled()
  })

  it("rejects a file over the size limit without opening the cropper", async () => {
    const wrapper = factory()
    const tooBig = new File([new Uint8Array(5 * 1024 * 1024 + 1)], "big.png", {
      type: "image/png"
    })
    await selectFile(wrapper, tooBig)

    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
    expect(dialogCreate).not.toHaveBeenCalled()
  })

  it("opens the cropper and uploads the cropped file on a valid selection", async () => {
    mockCropOk()
    const wrapper = factory()
    await selectFile(wrapper, pngFile())

    expect(dialogCreate).toHaveBeenCalled()
    expect(
      mockClient.getRequestHandler(UPLOAD_USER_AVATAR)
    ).toHaveBeenCalledWith({ id: "1", avatar: CROPPED_FILE })
    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "positive" })
    )
  })

  it("notifies a failure when the upload mutation rejects", async () => {
    mockClient
      .getRequestHandler(UPLOAD_USER_AVATAR)
      .mockRejectedValue(new Error("network"))
    mockCropOk()
    const wrapper = factory()
    await selectFile(wrapper, pngFile())

    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
  })

  it("removes the avatar and notifies success", async () => {
    const wrapper = factory({
      avatar: {
        __typename: "Avatar",
        thumb_url: "t",
        medium_url: "m",
        url: "u"
      }
    })
    await wrapper.find('[data-cy="avatar_remove_button"]').trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(DELETE_USER_AVATAR)
    ).toHaveBeenCalledWith({ id: "1" })
    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "positive" })
    )
  })

  it("notifies a failure when the delete mutation rejects", async () => {
    mockClient
      .getRequestHandler(DELETE_USER_AVATAR)
      .mockRejectedValue(new Error("network"))
    const wrapper = factory({
      avatar: {
        __typename: "Avatar",
        thumb_url: "t",
        medium_url: "m",
        url: "u"
      }
    })
    await wrapper.find('[data-cy="avatar_remove_button"]').trigger("click")
    await flushPromises()

    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
  })
})
