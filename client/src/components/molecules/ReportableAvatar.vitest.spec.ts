import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import { Dialog, Notify } from "quasar"
import type { Mock } from "vitest"
import { REPORT_USER_AVATAR } from "src/graphql/mutations"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

vi.mock("vue-i18n", () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

// Dialog.create / Notify.create attach only at app-install time; swap in fns.
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

// The trigger's visibility keys off the logged-in user; drive it per test.
const currentUser = { value: null as null | { id: string } }
vi.mock("src/use/user", () => ({
  useCurrentUser: () => ({ currentUser })
}))

// ReportAvatarDialog returns { reason } via onOk; resolve it directly.
function mockReportOk(reason: string | null = "spam") {
  dialogCreate.mockImplementation(() => ({
    onOk: (cb: (p: { reason: string | null }) => void) => {
      cb({ reason })
      return { onCancel: () => ({ onDismiss: () => ({}) }) }
    }
  }))
}

const AvatarImageStub = defineComponent({
  name: "AvatarImage",
  props: { user: Object, variant: String },
  setup: () => () => null
})
// q-menu hides its content until opened; passthrough so the report item renders.
const QMenuStub = defineComponent({
  name: "QMenu",
  setup:
    (_, { slots }) =>
    () =>
      h("div", slots.default?.())
})

installQuasarPlugin()
const mockClient = installApolloClient()

import ReportableAvatar from "./ReportableAvatar.vue"

const AVATAR = {
  __typename: "Avatar" as const,
  thumb_url: "t",
  medium_url: "m",
  url: "https://example.com/a.png"
}

function factory(user: Partial<avatarImageFragment> = {}) {
  const fullUser: avatarImageFragment = {
    __typename: "User",
    id: "2",
    email: "target@example.com",
    staged: null,
    avatar: AVATAR,
    ...user
  }
  return mount(ReportableAvatar, {
    props: { user: fullUser },
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { AvatarImage: AvatarImageStub, QMenu: QMenuStub }
    }
  })
}

const trigger = '[data-cy="reportable_avatar_trigger"]'
const reportItem = '[data-cy="reportable_avatar_menu_report"]'

describe("ReportableAvatar", () => {
  beforeEach(() => {
    mockClient.mockReset()
    dialogCreate.mockReset()
    notifyCreate.mockReset()
    currentUser.value = { id: "99" }
    mockClient.getRequestHandler(REPORT_USER_AVATAR).mockResolvedValue({
      data: {
        reportUserAvatar: {
          __typename: "AvatarReport",
          id: "5",
          status: "PENDING"
        }
      }
    })
  })

  it("hides the trigger when nobody is logged in", () => {
    currentUser.value = null
    expect(factory().find(trigger).exists()).toBe(false)
  })

  it("hides the trigger when the target has no uploaded avatar", () => {
    expect(factory({ avatar: null }).find(trigger).exists()).toBe(false)
  })

  it("hides the trigger when the avatar belongs to the current user", () => {
    currentUser.value = { id: "2" }
    expect(factory({ id: "2" }).find(trigger).exists()).toBe(false)
  })

  it("shows the trigger for another user's uploaded avatar", () => {
    expect(factory().find(trigger).exists()).toBe(true)
  })

  it("reports the avatar and notifies success", async () => {
    mockReportOk("inappropriate")
    const wrapper = factory()
    await wrapper.find(reportItem).trigger("click")
    await flushPromises()

    expect(dialogCreate).toHaveBeenCalled()
    expect(
      mockClient.getRequestHandler(REPORT_USER_AVATAR)
    ).toHaveBeenCalledWith({ userId: "2", reason: "inappropriate" })
    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "positive" })
    )
  })

  it("notifies a failure when the report mutation rejects", async () => {
    mockClient
      .getRequestHandler(REPORT_USER_AVATAR)
      .mockRejectedValue(new Error("network"))
    mockReportOk(null)
    const wrapper = factory()
    await wrapper.find(reportItem).trigger("click")
    await flushPromises()

    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
  })
})
