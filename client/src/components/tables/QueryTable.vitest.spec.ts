import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, vi, beforeEach } from "vitest"
import gql from "graphql-tag"
import QueryTable from "./QueryTable.vue"

const routerState = vi.hoisted(() => ({
  query: {} as Record<string, string>,
  replace: vi.fn()
}))
vi.mock("vue-router", () => ({
  useRoute: () => ({ query: routerState.query }),
  useRouter: () => ({ replace: routerState.replace })
}))

// te() drives the missing-header-translation warning branch.
let translationExists = true
vi.mock("vue-i18n", () => ({
  useI18n: () => ({
    t: (key: string) => key,
    te: () => translationExists
  })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

const PAGED = gql`
  query Paged($page: Int, $first: Int, $search: String) {
    items(page: $page, first: $first, search: $search) {
      data {
        id
        title
        status
      }
      paginatorInfo {
        total
        currentPage
        perPage
      }
    }
  }
`

const PAGED_SORTED = gql`
  query PagedSorted(
    $page: Int
    $first: Int
    $search: String
    $orderBy: [OrderBy!]
  ) {
    items(page: $page, first: $first, search: $search, orderBy: $orderBy) {
      data {
        id
        title
        status
      }
      paginatorInfo {
        total
        currentPage
        perPage
      }
    }
  }
`

const CellStub = defineComponent({
  name: "CellStub",
  props: { scope: { type: Object, required: true } },
  setup: (props) => () =>
    h("span", { class: "cell-stub" }, String(props.scope.value))
})

const columns = [
  { name: "title", field: "title", component: CellStub },
  { name: "status", field: "status" }
]

function resolveWith(rows: Record<string, unknown>[] = []) {
  mockClient.getRequestHandler(PAGED).mockResolvedValue({
    data: {
      items: {
        data: rows,
        paginatorInfo: { total: rows.length, currentPage: 1, perPage: 25 }
      }
    }
  })
}

function factory(props: Record<string, unknown> = {}) {
  return mount(QueryTable, {
    props: { query: PAGED, columns, tPrefix: "admin.table", ...props }
  })
}

beforeEach(() => {
  translationExists = true
  vi.restoreAllMocks()
  routerState.query = {}
  routerState.replace = vi.fn()
})

const sortableColumns = [
  { name: "title", field: "title", sortable: true },
  { name: "status", field: "status" }
]

// Mounts with an `item` slot so the table is gridable, and attaches to the
// document so q-menu portal content is queryable.
function gridFactory(
  props: Record<string, unknown> = {},
  slots: Record<string, unknown> = {}
) {
  return mount(QueryTable, {
    props: { query: PAGED, columns, tPrefix: "admin.table", ...props },
    slots: {
      item: (scope: { row?: { title?: string } }) =>
        h("div", { class: "grid-card" }, String(scope.row?.title ?? "")),
      ...slots
    },
    attachTo: document.body
  })
}

const findByText = (wrapper: ReturnType<typeof mount>, text: string) =>
  wrapper.findAll("button").find((b) => b.text().includes(text))

describe("QueryTable", () => {
  it("renders a search input when the query supports search", async () => {
    resolveWith()
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.find("input").exists()).toBe(true)
  })

  it("shows a create link when newTo is provided", async () => {
    resolveWith()
    const wrapper = factory({ newTo: { name: "create" } })
    await flushPromises()
    expect(wrapper.text()).toContain("buttons.create")
  })

  it("emits new when the create button is clicked in onNew mode", async () => {
    resolveWith()
    const wrapper = factory({ onNew: () => {} })
    await flushPromises()
    const createBtn = wrapper
      .findAll("button")
      .find((b) => b.text().includes("buttons.create"))
    await createBtn!.trigger("click")
    expect(wrapper.emitted("new")).toHaveLength(1)
  })

  it("resolves column headers from the tPrefix", async () => {
    resolveWith()
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).toContain("admin.table.headers.title")
    expect(wrapper.text()).toContain("admin.table.headers.status")
  })

  it("warns when a header translation is missing", async () => {
    translationExists = false
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {})
    resolveWith()
    factory()
    await flushPromises()
    expect(warn).toHaveBeenCalledWith(
      expect.stringContaining("missing header translation")
    )
  })

  it("renders the cell component for columns that declare one", async () => {
    resolveWith([{ id: "1", title: "Hello", status: "DRAFT" }])
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.find(".cell-stub").exists()).toBe(true)
    expect(wrapper.find(".cell-stub").text()).toBe("Hello")
  })

  it("honors label key overrides", async () => {
    resolveWith()
    const wrapper = factory({
      onNew: () => {},
      labels: { create: "custom.create" }
    })
    await flushPromises()
    expect(wrapper.text()).toContain("custom.create")
    expect(wrapper.text()).not.toContain("buttons.create")
  })

  it("returns undefined columns when none are supplied", async () => {
    resolveWith()
    const wrapper = factory({ columns: undefined })
    await flushPromises()
    // No columns configured -> no header cells from our set rendered
    expect(wrapper.text()).not.toContain("admin.table.headers.title")
  })
})

describe("QueryTable grid view", () => {
  it("hides the view toggle when no item slot is provided", async () => {
    resolveWith()
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.text()).not.toContain("tables.view.grid")
  })

  it("shows the view toggle when an item slot is provided", async () => {
    resolveWith()
    const wrapper = gridFactory()
    await flushPromises()
    expect(wrapper.text()).toContain("tables.view.grid")
  })

  it("switches to grid view when the toggle is clicked", async () => {
    resolveWith([{ id: "1", title: "Hello", status: "DRAFT" }])
    const wrapper = gridFactory()
    await flushPromises()
    // Table mode: item slot is not rendered.
    expect(wrapper.find(".grid-card").exists()).toBe(false)

    await findByText(wrapper, "tables.view.grid")!.trigger("click")
    await flushPromises()

    expect(wrapper.find(".grid-card").exists()).toBe(true)
    expect(wrapper.find(".grid-card").text()).toBe("Hello")
    // The toggle now offers a switch back to the table view.
    expect(wrapper.text()).toContain("tables.view.table")
    wrapper.unmount()
  })

  it("starts in grid view when the URL requests it", async () => {
    routerState.query = { view: "grid" }
    resolveWith([{ id: "1", title: "Hello", status: "DRAFT" }])
    const wrapper = gridFactory()
    await flushPromises()
    expect(wrapper.find(".grid-card").exists()).toBe(true)
    wrapper.unmount()
  })

  it("writes the view preference to the URL when syncUrl is set", async () => {
    resolveWith([{ id: "1", title: "Hello", status: "DRAFT" }])
    const wrapper = gridFactory({ syncUrl: true })
    await flushPromises()

    await findByText(wrapper, "tables.view.grid")!.trigger("click")
    await flushPromises()

    expect(routerState.replace).toHaveBeenCalledWith(
      expect.objectContaining({
        query: expect.objectContaining({ view: "grid" })
      })
    )
    wrapper.unmount()
  })
})

describe("QueryTable sort menu", () => {
  it("shows the sort menu only in grid view with sortable columns", async () => {
    resolveWith([{ id: "1", title: "Hello", status: "DRAFT" }])
    const wrapper = gridFactory({ columns: sortableColumns })
    await flushPromises()
    // Table mode: no sort menu.
    expect(wrapper.text()).not.toContain("tables.sort.label")

    await findByText(wrapper, "tables.view.grid")!.trigger("click")
    await flushPromises()
    expect(wrapper.text()).toContain("tables.sort.label")
    wrapper.unmount()
  })

  it("hides the sort menu in grid view when no column is sortable", async () => {
    resolveWith([{ id: "1", title: "Hello", status: "DRAFT" }])
    const wrapper = gridFactory({ columns })
    await flushPromises()

    await findByText(wrapper, "tables.view.grid")!.trigger("click")
    await flushPromises()
    expect(wrapper.text()).not.toContain("tables.sort.label")
    wrapper.unmount()
  })

  it("applies a chosen sort option as an orderBy query variable", async () => {
    const handler = mockClient
      .getRequestHandler(PAGED_SORTED)
      .mockResolvedValue({
        data: {
          items: {
            data: [],
            paginatorInfo: { total: 0, currentPage: 1, perPage: 25 }
          }
        }
      })
    const wrapper = gridFactory({
      query: PAGED_SORTED,
      columns: sortableColumns
    })
    await flushPromises()
    await findByText(wrapper, "tables.view.grid")!.trigger("click")
    await flushPromises()

    await findByText(wrapper, "tables.sort.label")!.trigger("click")
    await flushPromises()

    // Two options per sortable column (asc, desc); only `title` is sortable.
    const items = Array.from(
      document.querySelectorAll<HTMLElement>(".q-menu .q-item")
    )
    expect(items).toHaveLength(2)

    handler.mockClear()
    items[1].click() // descending
    await flushPromises()

    const lastCall = handler.mock.calls.at(-1)?.[0] as Record<string, unknown>
    expect(lastCall).toMatchObject({
      orderBy: [{ column: "TITLE", order: "DESC" }],
      page: 1
    })
    wrapper.unmount()
  })
})
