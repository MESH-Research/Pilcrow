import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h, ref } from "vue"
import { describe, expect, it, vi, beforeEach } from "vitest"

let routeQuery: Record<string, unknown> = {}
const routerMock = { push: vi.fn(), replace: vi.fn() }

vi.mock("vue-router", () => ({
  useRoute: () => ({ params: { id: "5" }, query: routeQuery }),
  useRouter: () => routerMock
}))

vi.mock("vue-i18n", () => ({
  useI18n: () => ({ t: (key: string) => key, te: () => true })
}))

// Lightweight QueryTable stand-in: renders the filter/no-data slots so
// the page's URL-sync watcher runs, exposes a writable `page` for the
// queryTableRef, and surfaces the columns/variables props for inspection.
const QueryTableStub = defineComponent({
  name: "QueryTable",
  props: {
    query: { type: Object, default: undefined },
    variables: { type: Object, default: () => ({}) },
    columns: { type: Array, default: () => [] },
    field: { type: String, default: "" },
    tPrefix: { type: String, default: "" },
    refreshBtn: { type: Boolean, default: true },
    syncUrl: { type: Boolean, default: false }
  },
  emits: ["row-click", "new"],
  setup(_props, { slots, expose }) {
    const page = ref(99)
    expose({ page })
    return () =>
      h("div", { class: "qt-stub" }, [
        slots["top-after"]?.(),
        slots["no-data"]?.()
      ])
  }
})

installQuasarPlugin()

import SubmissionsPage from "./submissions.vue"

function factory() {
  return mount(SubmissionsPage, {
    global: {
      stubs: {
        QueryTable: QueryTableStub,
        SubmissionsFilterPanel: true,
        UserSubmissionsNoDataSlot: true
      },
      mocks: { $t: (token: string) => token }
    }
  })
}

beforeEach(() => {
  routeQuery = {}
  routerMock.push.mockReset()
  routerMock.replace.mockReset()
})

const sampleRow = {
  role: "reviewer",
  submission: {
    id: "7",
    title: "A Study",
    status: 1,
    created_at: "2024-01-02T00:00:00Z",
    updated_at: "2024-01-03T00:00:00Z",
    publication: { id: "3", name: "Journal X" }
  }
}

function stub(wrapper: ReturnType<typeof factory>) {
  return wrapper.findComponent(QueryTableStub)
}

describe("admin user submissions page", () => {
  it("passes the route id through to the query variables", () => {
    const wrapper = factory()
    expect((stub(wrapper).props("variables") as { id: string }).id).toBe("5")
  })

  it("parses bracketed filter lists from the route query", () => {
    routeQuery = { status: "[DRAFT,REJECTED]", roles: "[reviewer]" }
    const wrapper = factory()
    const vars = stub(wrapper).props("variables") as {
      status: string[]
      roles: string[]
    }
    expect(vars.status).toEqual(["DRAFT", "REJECTED"])
    expect(vars.roles).toEqual(["reviewer"])
  })

  it("parses a plain (unbracketed) filter value", () => {
    routeQuery = { status: "DRAFT" }
    const wrapper = factory()
    expect(
      (stub(wrapper).props("variables") as { status: string[] }).status
    ).toEqual(["DRAFT"])
  })

  it("parses the array form of a query param", () => {
    routeQuery = { roles: ["[reviewer,editor]"] }
    const wrapper = factory()
    expect(
      (stub(wrapper).props("variables") as { roles: string[] }).roles
    ).toEqual(["reviewer", "editor"])
  })

  it("wraps a single publication id into an array, or omits it", () => {
    routeQuery = { publication: "42" }
    const withPub = factory()
    expect(
      stub(withPub).props("variables") as { publication?: string[] }
    ).toMatchObject({ publication: ["42"] })

    routeQuery = {}
    const withoutPub = factory()
    expect(
      (stub(withoutPub).props("variables") as { publication?: string[] })
        .publication
    ).toBeUndefined()
  })

  it("navigates to the submission on row click", () => {
    const wrapper = factory()
    stub(wrapper).vm.$emit("row-click", new Event("click"), sampleRow, 0)
    expect(routerMock.push).toHaveBeenCalledWith({
      name: "submission:details",
      params: { id: "7" }
    })
  })

  it("derives column values from each row", () => {
    const wrapper = factory()
    const columns = stub(wrapper).props("columns") as {
      name: string
      field: (row: unknown) => unknown
      aside?: (row: unknown) => unknown
    }[]
    const byName = (n: string) => columns.find((c) => c.name === n)!

    expect(byName("title").field(sampleRow)).toBe("A Study")
    expect(byName("title").aside!(sampleRow)).toBe("Journal X")
    expect(byName("role").field(sampleRow)).toBe(
      "admin.users.details.roles.reviewer"
    )
    expect(byName("status").field(sampleRow)).toBe("submission.status.1")
    expect(byName("created_at").field(sampleRow)).toBe("2024-01-02T00:00:00Z")
    expect(byName("updated_at").field(sampleRow)).toBe("2024-01-03T00:00:00Z")
  })

  it("syncs non-default filters back to the URL query", async () => {
    routeQuery = { status: "[DRAFT]", roles: "[reviewer]", publication: "9" }
    const wrapper = factory()
    // Re-assigning a filter ref triggers the watcher.
    const vm = wrapper.vm as unknown as {
      statusFilter: string[]
      publicationFilter: string | null
    }
    vm.statusFilter = ["REJECTED"]
    await flushPromises()
    expect(routerMock.replace).toHaveBeenCalled()
    const lastArg = routerMock.replace.mock.calls.at(-1)![0] as {
      query: Record<string, string>
    }
    expect(lastArg.query.status).toBe("[REJECTED]")
    expect(lastArg.query.publication).toBe("9")
  })

  it("drops default filters from the URL query", async () => {
    routeQuery = { status: "[DRAFT]" }
    const wrapper = factory()
    const { defaultOptions } =
      await import("src/pages/Admin/components/SubmissionsFilterPanelStatus.vue")
    // Setting the status filter back to the full default set should remove
    // the status param from the URL rather than serialize it.
    const vm = wrapper.vm as unknown as { statusFilter: string[] }
    vm.statusFilter = [...defaultOptions]
    await flushPromises()
    const lastArg = routerMock.replace.mock.calls.at(-1)![0] as {
      query: Record<string, string>
    }
    expect(lastArg.query.status).toBeUndefined()
  })
})
