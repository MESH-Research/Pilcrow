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
async function resolveChildren(
  router: Router,
  parent: string = "labs",
  slice?: number
): Promise<ChildRoute[]> {
  let children!: ComputedRef<ChildRoute[]>
  const Harness = defineComponent({
    setup() {
      // Test-only route names sit outside the app's typed route union.
      const route = { name: parent } as unknown as RouteLocationRaw
      const { childrenOf } = useNavigation()
      children =
        slice === undefined ? childrenOf(route) : childrenOf(route, slice)
      return () => h("div")
    }
  })
  await router.push({ name: parent } as RouteLocationRaw)
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

  it("falls back to the route name when no navigation label is set", async () => {
    const bareRouter = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: "/p",
          name: "p",
          component: Dummy,
          children: [
            {
              path: "/p/bare",
              name: "p:bare",
              component: Dummy,
              // navigation present (so it's not filtered) but no label
              meta: { navigation: {} as { label: string | false } }
            }
          ]
        }
      ]
    })
    const children = await resolveChildren(bareRouter, "p")
    expect(byName(children, "p:bare")?.label).toBe("p:bare")
  })

  it("returns an empty list when the matched route has no children", async () => {
    const childlessRouter = createRouter({
      history: createMemoryHistory(),
      routes: [{ path: "/leaf", name: "leaf", component: Dummy }]
    })
    const children = await resolveChildren(childlessRouter, "leaf")
    expect(children).toEqual([])
  })

  it("selects the matched entry via the slice argument", async () => {
    // Nested layout: childrenOf(slice=0) reads the outer match's children,
    // default slice(-1) reads the innermost match's children.
    const nestedRouter = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: "/outer",
          name: "outer",
          component: Dummy,
          children: [
            {
              path: "inner",
              name: "inner",
              component: Dummy,
              children: [
                {
                  path: "leaf",
                  name: "inner:leaf",
                  component: Dummy,
                  meta: { navigation: { label: "Leaf" } }
                }
              ]
            }
          ]
        }
      ]
    })
    // Resolve on the deepest route so matched holds outer → inner → leaf.
    const deepDefault = await resolveChildren(nestedRouter, "inner:leaf")
    expect(names(deepDefault)).toEqual([])
    const deepFromStart = await resolveChildren(nestedRouter, "inner:leaf", 0)
    expect(names(deepFromStart)).toEqual(["inner"])
  })

  it("does not descend into a section's nested param route", async () => {
    // The admin tree nests drill-down routes (`users/:id`) *under* their
    // section rather than alongside the dashboard tiles. childrenOf only
    // reads one level, so the param route is never a direct child and
    // never gets resolved — no "Missing required param" throw, and only
    // the resolvable section appears.
    const sectionRouter = createRouter({
      history: createMemoryHistory(),
      routes: [
        {
          path: "/admin",
          name: "admin",
          component: Dummy,
          children: [
            {
              path: "users",
              name: "admin:users-section",
              component: Dummy,
              meta: { navigation: { label: "Users" } },
              children: [
                { path: "", name: "admin:users", component: Dummy },
                { path: ":id", name: "admin:user:id", component: Dummy }
              ]
            },
            {
              path: "publications",
              name: "admin:publications",
              component: Dummy,
              meta: { navigation: { label: "Publications" } }
            }
          ]
        }
      ]
    })
    const children = await resolveChildren(sectionRouter, "admin")
    expect(names(children)).toEqual([
      "admin:users-section",
      "admin:publications"
    ])
  })
})
