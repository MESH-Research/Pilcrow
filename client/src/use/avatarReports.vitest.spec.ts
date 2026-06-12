import { mount } from "vue-composable-tester"
import { createMockClient } from "app/test/vitest/utils"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { GetPendingAvatarReportCountDocument } from "src/graphql/generated/graphql"
import { flushPromises } from "@vue/test-utils"
import { provide } from "vue"
import { describe, test, expect, vi, beforeEach } from "vitest"

// Drive the moderator gate per test; the composable skips the query for
// non-moderators.
const can = vi.fn()
vi.mock("./user", () => ({
  useCurrentUser: () => ({ can })
}))

import { useAvatarReportsPendingCount } from "./avatarReports"

function mountComposable(total: number | null) {
  const mockClient = createMockClient({ devtools: { enabled: false } })
  const handler = vi
    .fn()
    .mockResolvedValue(
      total === null
        ? { data: { avatarReports: { paginatorInfo: { total: 0 } } } }
        : { data: { avatarReports: { paginatorInfo: { total } } } }
    )
  mockClient.setRequestHandler(GetPendingAvatarReportCountDocument, handler)
  const { result } = mount(() => useAvatarReportsPendingCount(), {
    provider: () => {
      provide(DefaultApolloClient, mockClient)
    }
  })
  return { result, handler }
}

describe("useAvatarReportsPendingCount", () => {
  beforeEach(() => {
    can.mockReset()
  })

  test("skips the query and reports zero for a non-moderator", async () => {
    can.mockReturnValue(false)
    const { result, handler } = mountComposable(7)
    await flushPromises()

    expect(can).toHaveBeenCalledWith("moderate avatars")
    expect(result.canModerate.value).toBe(false)
    expect(handler).not.toHaveBeenCalled()
    expect(result.count.value).toBe(0)
  })

  test("exposes the pending total for a moderator", async () => {
    can.mockReturnValue(true)
    const { result, handler } = mountComposable(3)
    await flushPromises()

    expect(result.canModerate.value).toBe(true)
    expect(handler).toHaveBeenCalled()
    expect(result.count.value).toBe(3)
  })

  test("returns a callable refetch", async () => {
    can.mockReturnValue(true)
    const { result } = mountComposable(1)
    await flushPromises()

    expect(typeof result.refetch).toBe("function")
  })
})
