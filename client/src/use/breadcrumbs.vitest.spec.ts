import { describe, expect, it, vi, beforeAll } from "vitest"
import { ref, nextTick } from "vue"

const matched = ref<Array<{ name?: string; meta: { crumb?: unknown } }>>([])

vi.mock("vue-router", () => ({
  useRoute: () => ({
    get matched() {
      return matched.value
    }
  }),
  useRouter: () => ({
    resolve: (to: unknown) => to
  })
}))

import { useCrumbs, setCrumbLabel } from "./breadcrumbs"

describe("useCrumbs", () => {
  let crumbs: ReturnType<typeof useCrumbs>["crumbs"]
  let count: ReturnType<typeof useCrumbs>["count"]

  beforeAll(() => {
    const api = useCrumbs()
    crumbs = api.crumbs
    count = api.count
  })

  it("emits a crumb per matched route with crumb meta", async () => {
    matched.value = [
      { name: "admin", meta: { crumb: { label: "admin.title" } } },
      { name: "admin-users", meta: { crumb: { label: "admin.users.title" } } }
    ]
    await nextTick()
    expect(crumbs.value).toHaveLength(2)
    expect(crumbs.value[0].label.value).toBe("admin.title")
    expect(crumbs.value[1].label.value).toBe("admin.users.title")
    expect(count.value).toBe(2)
  })

  it("skips routes without crumb meta", async () => {
    matched.value = [
      { name: "root", meta: {} },
      { name: "admin", meta: { crumb: { label: "admin.title" } } }
    ]
    await nextTick()
    expect(crumbs.value).toHaveLength(1)
    expect(crumbs.value[0].label.value).toBe("admin.title")
  })

  it("expands an array of crumbs from a single matched route", async () => {
    matched.value = [
      {
        name: "admin-user-detail",
        meta: {
          crumb: [
            { label: "admin.users.title", to: { name: "admin-users" } },
            { label: "user.detail" }
          ]
        }
      }
    ]
    await nextTick()
    expect(crumbs.value).toHaveLength(2)
    expect(crumbs.value[0].label.value).toBe("admin.users.title")
    expect(crumbs.value[1].label.value).toBe("user.detail")
  })

  it("setCrumbLabel overrides the final crumb a route contributes", async () => {
    matched.value = [
      { name: "admin-user-detail", meta: { crumb: { label: "user.detail" } } }
    ]
    await nextTick()
    expect(crumbs.value[0].label.value).toBe("user.detail")

    setCrumbLabel("admin-user-detail" as never, "Alice")
    await nextTick()
    expect(crumbs.value[0].label.value).toBe("Alice")
  })

  it("override only applies to the route's last crumb in an array", async () => {
    matched.value = [
      {
        name: "admin-user-detail",
        meta: {
          crumb: [{ label: "admin.users.title" }, { label: "user.detail" }]
        }
      }
    ]
    setCrumbLabel("admin-user-detail" as never, "Alice")
    await nextTick()
    expect(crumbs.value[0].label.value).toBe("admin.users.title")
    expect(crumbs.value[1].label.value).toBe("Alice")
  })
})
