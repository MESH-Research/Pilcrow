import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import { Dialog, Notify } from "quasar"
import type { Mock } from "vitest"
import {
  DISMISS_AVATAR_REPORT,
  RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR
} from "src/graphql/mutations"

vi.mock("vue-i18n", () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

// Dialog.create / Notify.create are only attached to the Quasar singletons at
// app-install time, which here happens inside mount() — too late for spies set
// up in the test body. Replace them with plain vi.fn()s up front instead.
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

// Default: confirm dialog resolves OK with blockFutureUploads false; per-test
// overrides set the payload. Returns the chainable stub Quasar dialogs expose.
function mockDialogOk(blockFutureUploads = false) {
  dialogCreate.mockImplementation(() => ({
    onOk: (cb: (payload: { blockFutureUploads: boolean }) => void) => {
      cb({ blockFutureUploads })
      return { onCancel: () => ({ onDismiss: () => ({}) }) }
    }
  }))
}

// The pending-count composable runs its own query; stub it so this spec
// stays on the moderation-queue wiring. refetch is a spy we assert on.
const refetchPendingCount = vi.fn()
vi.mock("src/use/avatarReports", () => ({
  useAvatarReportsPendingCount: () => ({
    count: { value: 0 },
    refetch: refetchPendingCount
  })
}))

// QueryTable owns the listing/refetch; stub it down to the actions slot so
// the per-row dismiss/remove buttons are reachable with a fake PENDING row.
const PENDING_ROW = { id: "42", status: "PENDING" }
const QueryTableStub = defineComponent({
  name: "QueryTable",
  setup(_, { slots }) {
    return () =>
      h("div", { class: "query-table-stub" }, [
        slots["body-cell-actions"]?.({ row: PENDING_ROW })
      ])
  }
})

// QTd reads QTable-internal column metadata it never receives outside a real
// table; passthrough stub so the slot's buttons render.
const QTdStub = defineComponent({
  name: "QTd",
  setup:
    (_, { slots }) =>
    () =>
      h("td", slots.default?.())
})

installQuasarPlugin()
const mockClient = installApolloClient()

import AvatarReportsPage from "./avatar-reports.vue"

function factory() {
  return mount(AvatarReportsPage, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: { QueryTable: QueryTableStub, QTd: QTdStub }
    }
  })
}

describe("admin avatar-reports page", () => {
  beforeEach(() => {
    mockClient.mockReset()
    refetchPendingCount.mockReset()
    dialogCreate.mockReset()
    notifyCreate.mockReset()
    mockClient.getRequestHandler(DISMISS_AVATAR_REPORT).mockResolvedValue({
      data: {
        dismissAvatarReport: {
          __typename: "AvatarReport",
          id: "42",
          status: "DISMISSED",
          resolution_notes: null,
          resolved_at: "2026-06-12T00:00:00Z"
        }
      }
    })
    mockClient
      .getRequestHandler(RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR)
      .mockResolvedValue({
        data: {
          resolveAvatarReportAndRemoveAvatar: {
            __typename: "AvatarReport",
            id: "42",
            status: "REMOVED",
            resolution_notes: null,
            resolved_at: "2026-06-12T00:00:00Z"
          }
        }
      })
  })

  it("dismisses a pending report, refetches the badge, and notifies", async () => {
    const wrapper = factory()

    await wrapper.find('[data-cy="avatar_report_dismiss"]').trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(DISMISS_AVATAR_REPORT)
    ).toHaveBeenCalledWith({ id: "42" })
    expect(refetchPendingCount).toHaveBeenCalled()
    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "positive" })
    )
  })

  it("notifies a failure when dismiss rejects", async () => {
    mockClient
      .getRequestHandler(DISMISS_AVATAR_REPORT)
      .mockRejectedValue(new Error("network"))
    const wrapper = factory()

    await wrapper.find('[data-cy="avatar_report_dismiss"]').trigger("click")
    await flushPromises()

    expect(refetchPendingCount).not.toHaveBeenCalled()
    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
  })

  it("removes via the confirm dialog, passing blockFutureUploads through", async () => {
    mockDialogOk(true)
    const wrapper = factory()

    await wrapper.find('[data-cy="avatar_report_remove"]').trigger("click")
    await flushPromises()

    expect(
      mockClient.getRequestHandler(RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR)
    ).toHaveBeenCalledWith({ id: "42", blockFutureUploads: true })
    expect(refetchPendingCount).toHaveBeenCalled()
    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "positive" })
    )
  })

  it("notifies a failure when the remove mutation rejects", async () => {
    mockClient
      .getRequestHandler(RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR)
      .mockRejectedValue(new Error("network"))
    mockDialogOk(false)
    const wrapper = factory()

    await wrapper.find('[data-cy="avatar_report_remove"]').trigger("click")
    await flushPromises()

    expect(notifyCreate).toHaveBeenCalledWith(
      expect.objectContaining({ type: "negative" })
    )
  })
})
