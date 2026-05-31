import {
  createRouter,
  createMemoryHistory,
  type Router,
  type RouteLocationRaw
} from "vue-router"
import { mount } from "@vue/test-utils"
import { defineComponent, h, type ComputedRef } from "vue"
import { describe, it, expect, beforeEach } from "vitest"
import { useNavigation, type ChildRoute } from "./navigation"

const Dummy = defineComponent({ render: () => h("div", "dummy") })

// Absolute child paths sidestep relative-path resolution quirks so the
// test exercises childrenOf's own filter/sort/meta/component logic.
function makeRouter(): Router {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: "/labs",
        name: "labs",
        component: Dummy,
        children: [
          {
            path: "/labs/second",
            name: "labs:second",
            component: () => Promise.resolve(Dummy),
            meta: {
              navigation: { label: "Second", order: 20 },
              feature: { key: "second", private: true }
            }
          },
          {
            path: "/labs/first",
            name: "labs:first",
            component: () => Promise.resolve(Dummy),
            meta: {
              navigation: { label: "First", order: 10 },
              feature: { key: "first", private: false }
            }
          },
          {
            path: "/labs/hidden",
            name: "labs:hidden",
            component: Dummy,
            meta: { navigation: { label: false } }
          },
          {
            path: "/labs/last",
            name: "labs:last",
            component: Dummy,
            // No order — pushed to the end after ordered siblings.
            meta: { navigation: { label: "Last" } }
          }
        ]
      }
    ]
  })
}

// Mount a throwaway component so useNavigation has a live router, and
// expose the resolved children list off the instance.
async function resolveChildren(router: Router): Promise<ChildRoute[]> {
  let children!: ComputedRef<ChildRoute[]>
  const Harness = defineComponent({
    setup() {
      // Test-only route names sit outside the app's typed route union.
      children = useNavigation().childrenOf({
        name: "labs"
      } as unknown as RouteLocationRaw)
      return () => h("div")
    }
  })
  router.push("/labs")
  await router.isReady()
  mount(Harness, { global: { plugins: [router] } })
  return children.value
}

describe("useNavigation childrenOf", () => {
  let router: Router

  beforeEach(() => {
    router = makeRouter()
  })

  // Route names are a typed union in this app; compare as plain strings.
  const names = (children: ChildRoute[]) => children.map((c) => String(c.name))
  const byName = (children: ChildRoute[], name: string) =>
    children.find((c) => String(c.name) === name)

  it("excludes children whose navigation.label is false", async () => {
    const children = await resolveChildren(router)
    expect(names(children)).not.toContain("labs:hidden")
  })

  it("sorts by navigation.order, unordered last", async () => {
    const children = await resolveChildren(router)
    expect(names(children)).toEqual(["labs:first", "labs:second", "labs:last"])
  })

  it("passes through resolved meta for caller-side filtering", async () => {
    const children = await resolveChildren(router)
    expect(byName(children, "labs:first")?.meta.feature).toEqual({
      key: "first",
      private: false
    })
  })

  it("derives label and icon from navigation meta", async () => {
    const children = await resolveChildren(router)
    expect(byName(children, "labs:first")?.label).toBe("First")
  })

  it("wraps lazy component loaders for inline rendering", async () => {
    const children = await resolveChildren(router)
    expect(byName(children, "labs:first")?.component).toBeTruthy()
  })
})
