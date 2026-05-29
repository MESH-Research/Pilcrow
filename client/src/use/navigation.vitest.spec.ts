import { describe, expect, it, vi } from "vitest"

type Resolvable = {
  name?: string
  meta?: { navigation?: { label?: string | false; icon?: string; order?: number } }
  matched?: Array<{ children?: unknown[] }>
}

// Identity-style resolver: the navigation composable calls resolve() once on
// the route it's handed and once per child, so the mock just normalises shape.
const resolve = vi.fn((to: Resolvable) => ({
  name: to.name,
  meta: to.meta ?? {},
  matched: to.matched ?? []
}))

vi.mock("vue-router", () => ({
  useRouter: () => ({ resolve })
}))

import { useNavigation } from "./navigation"

function parentWith(children: Resolvable[]): Resolvable {
  return { matched: [{ children }] }
}

describe("useNavigation childrenOf", () => {
  it("maps navigable children to ChildRoute entries", () => {
    const { childrenOf } = useNavigation()
    const route = parentWith([
      {
        name: "users",
        meta: { navigation: { label: "admin.users.title", icon: "people" } }
      }
    ])
    const result = childrenOf(route).value
    expect(result).toEqual([
      {
        name: "users",
        label: "admin.users.title",
        icon: "people",
        url: { name: "users" }
      }
    ])
  })

  it("sorts by order, lowest first", () => {
    const { childrenOf } = useNavigation()
    const route = parentWith([
      { name: "c", meta: { navigation: { label: "c", order: 30 } } },
      { name: "a", meta: { navigation: { label: "a", order: 10 } } },
      { name: "b", meta: { navigation: { label: "b", order: 20 } } }
    ])
    expect(childrenOf(route).value.map((r) => r.name)).toEqual(["a", "b", "c"])
  })

  it("pushes children without order to the end, preserving source order", () => {
    const { childrenOf } = useNavigation()
    const route = parentWith([
      { name: "noOrderFirst", meta: { navigation: { label: "x" } } },
      { name: "ordered", meta: { navigation: { label: "y", order: 5 } } },
      { name: "noOrderSecond", meta: { navigation: { label: "z" } } }
    ])
    expect(childrenOf(route).value.map((r) => r.name)).toEqual([
      "ordered",
      "noOrderFirst",
      "noOrderSecond"
    ])
  })

  it("excludes children whose navigation label is false", () => {
    const { childrenOf } = useNavigation()
    const route = parentWith([
      { name: "hidden", meta: { navigation: { label: false } } },
      { name: "shown", meta: { navigation: { label: "shown" } } }
    ])
    expect(childrenOf(route).value.map((r) => r.name)).toEqual(["shown"])
  })

  it("falls back to the route name when no navigation label is set", () => {
    const { childrenOf } = useNavigation()
    const route = parentWith([{ name: "bare", meta: {} }])
    const result = childrenOf(route).value
    expect(result).toEqual([
      { name: "bare", label: "bare", icon: undefined, url: { name: "bare" } }
    ])
  })

  it("returns an empty list when the matched route has no children", () => {
    const { childrenOf } = useNavigation()
    expect(childrenOf({ matched: [{}] }).value).toEqual([])
  })

  it("returns an empty list when nothing is matched", () => {
    const { childrenOf } = useNavigation()
    expect(childrenOf({ matched: [] }).value).toEqual([])
  })

  it("selects the matched entry via the slice argument", () => {
    const { childrenOf } = useNavigation()
    const route: Resolvable = {
      matched: [
        { children: [{ name: "deep", meta: { navigation: { label: "deep" } } }] },
        { children: [{ name: "leaf", meta: { navigation: { label: "leaf" } } }] }
      ]
    }
    // default slice(-1) takes the last matched entry's children
    expect(childrenOf(route).value.map((r) => r.name)).toEqual(["leaf"])
    // slice(0) takes from the first matched entry onward
    expect(childrenOf(route, 0).value.map((r) => r.name)).toEqual(["deep"])
  })
})
