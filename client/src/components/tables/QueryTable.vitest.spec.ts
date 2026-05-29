import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h } from "vue"
import { describe, expect, it, vi, beforeEach } from "vitest"
import gql from "graphql-tag"
import QueryTable from "./QueryTable.vue"

vi.mock("vue-router", () => ({
  useRoute: () => ({ query: {} }),
  useRouter: () => ({ replace: vi.fn() })
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
})

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
