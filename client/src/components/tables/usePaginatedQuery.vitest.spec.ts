import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h, ref, type Ref } from "vue"
import { describe, expect, it, vi } from "vitest"
import gql from "graphql-tag"
import { usePaginatedQuery } from "./usePaginatedQuery"

vi.mock("vue-router", () => ({
  useRoute: () => ({ query: {} }),
  useRouter: () => ({ replace: vi.fn() })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

const PAGED = gql`
  query Paged($page: Int, $first: Int, $search: String, $orderBy: [OrderBy!]) {
    items(page: $page, first: $first, search: $search, orderBy: $orderBy) {
      data {
        id
        name
      }
      paginatorInfo {
        total
        currentPage
        perPage
      }
    }
  }
`

const UNPAGED = gql`
  query Unpaged {
    items {
      data {
        id
      }
    }
  }
`

const NESTED = gql`
  query Nested($page: Int, $first: Int) {
    wrapper {
      items(page: $page, first: $first) {
        data {
          id
          name
        }
        paginatorInfo {
          total
          currentPage
          perPage
        }
      }
    }
  }
`

type ApiShape = ReturnType<typeof usePaginatedQuery>
type Captured = { api: ApiShape | null }

function host(
  query: typeof PAGED,
  options: Parameters<typeof usePaginatedQuery>[1] = {}
) {
  const captured: Captured = { api: null }
  const Host = defineComponent({
    setup() {
      captured.api = usePaginatedQuery(query, options)
      return () => h("div")
    }
  })
  const wrapper = mount(Host)
  return { wrapper, captured }
}

describe("usePaginatedQuery", () => {
  it("returns default pagination state", () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED)
    expect(captured.api!.pagination.value).toEqual({
      sortBy: "",
      descending: false,
      page: 1,
      rowsPerPage: 25,
      rowsNumber: 0
    })
  })

  it("honors defaultSort option", () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED, {
      defaultSort: { sortBy: "name", descending: true }
    })
    expect(captured.api!.pagination.value.sortBy).toBe("name")
    expect(captured.api!.pagination.value.descending).toBe(true)
  })

  it("populates rows and paginatorInfo from the result", async () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: {
        items: {
          data: [{ id: "1", name: "a" }],
          paginatorInfo: { total: 7, currentPage: 1, perPage: 10 }
        }
      }
    })
    const { captured } = host(PAGED)
    await flushPromises()
    expect(captured.api!.rows.value).toHaveLength(1)
    expect(captured.api!.pagination.value.rowsNumber).toBe(7)
    expect(captured.api!.pagination.value.rowsPerPage).toBe(10)
  })

  it("sends sort and search variables to the query", async () => {
    const handler = mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED)
    captured.api!.pagination.value.sortBy = "name"
    captured.api!.pagination.value.descending = true
    ;(captured.api!.filter as Ref<string>).value = "hello"
    await flushPromises()

    const lastCall = handler.mock.calls.at(-1)?.[0] as Record<string, unknown>
    expect(lastCall).toMatchObject({
      first: 25,
      page: 1,
      search: "hello",
      orderBy: [{ column: "NAME", order: "DESC" }]
    })
  })

  it("onRequest updates pagination state", () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED)
    captured.api!.onRequest({
      pagination: { sortBy: "id", descending: true, page: 3, rowsPerPage: 50 },
      getCellValue: () => null
    })
    expect(captured.api!.pagination.value).toMatchObject({
      sortBy: "id",
      descending: true,
      page: 3,
      rowsPerPage: 50
    })
  })

  it("disables paginationModel when query has no page variable", () => {
    mockClient.getRequestHandler(UNPAGED).mockResolvedValue({
      data: { items: { data: [] } }
    })
    const { captured } = host(UNPAGED)
    expect(captured.api!.paginationModel.value).toBeUndefined()
  })

  it("page setter parses string input", () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED)
    ;(captured.api!.page as unknown as { value: number | string }).value = "3"
    expect(captured.api!.pagination.value.page).toBe(3)
  })

  it("page setter ignores values that are not greater than zero", () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED)
    const setter = captured.api!.page as unknown as { value: number | string }
    setter.value = 4
    setter.value = 0
    setter.value = -2
    expect(captured.api!.pagination.value.page).toBe(4)
  })

  it("reads the paginated field from a dotted field path", async () => {
    mockClient.getRequestHandler(NESTED).mockResolvedValue({
      data: {
        wrapper: {
          items: {
            data: [{ id: "1", name: "a" }],
            paginatorInfo: { total: 3, currentPage: 1, perPage: 5 }
          }
        }
      }
    })
    const { captured } = host(NESTED, { field: "wrapper.items" })
    await flushPromises()
    expect(captured.api!.rows.value).toHaveLength(1)
    expect(captured.api!.pagination.value.rowsNumber).toBe(3)
    expect(captured.api!.pagination.value.rowsPerPage).toBe(5)
  })

  it("merges caller-supplied variables into the query", async () => {
    const handler = mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    host(PAGED, {
      variables: { orderBy: [{ column: "CUSTOM", order: "ASC" }] }
    })
    await flushPromises()
    const lastCall = handler.mock.calls.at(-1)?.[0] as Record<string, unknown>
    expect(lastCall).toMatchObject({
      orderBy: [{ column: "CUSTOM", order: "ASC" }],
      first: 25,
      page: 1
    })
  })

  it("does not run the query while disabled", async () => {
    const handler = mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: {
        items: {
          data: [{ id: "1", name: "a" }],
          paginatorInfo: { total: 1, currentPage: 1, perPage: 25 }
        }
      }
    })
    // The handler is shared across the file; only count calls from here on.
    handler.mockClear()
    const { captured } = host(PAGED, { enabled: false })
    await flushPromises()
    expect(handler).not.toHaveBeenCalled()
    expect(captured.api!.rows.value).toEqual([])
    expect(captured.api!.pagination.value.rowsNumber).toBe(0)
  })

  it("runs the query once enabled flips to true", async () => {
    const handler = mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: {
        items: {
          data: [{ id: "1", name: "a" }],
          paginatorInfo: { total: 1, currentPage: 1, perPage: 25 }
        }
      }
    })
    handler.mockClear()
    const enabled = ref(false)
    const { captured } = host(PAGED, { enabled })
    await flushPromises()
    expect(handler).not.toHaveBeenCalled()

    enabled.value = true
    await flushPromises()
    expect(handler).toHaveBeenCalled()
    expect(captured.api!.rows.value).toHaveLength(1)
  })

  it("clears rows when disabled after having loaded data", async () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: {
        items: {
          data: [{ id: "1", name: "a" }],
          paginatorInfo: { total: 1, currentPage: 1, perPage: 25 }
        }
      }
    })
    const enabled = ref(true)
    const { captured } = host(PAGED, { enabled })
    await flushPromises()
    expect(captured.api!.rows.value).toHaveLength(1)

    enabled.value = false
    await flushPromises()
    expect(captured.api!.rows.value).toEqual([])
    expect(captured.api!.pagination.value.rowsNumber).toBe(0)
  })

  it("paginationModel setter applies a new pagination object", () => {
    mockClient.getRequestHandler(PAGED).mockResolvedValue({
      data: { items: { data: [], paginatorInfo: null } }
    })
    const { captured } = host(PAGED)
    const next = {
      sortBy: "name",
      descending: true,
      page: 2,
      rowsPerPage: 50,
      rowsNumber: 9
    }
    captured.api!.paginationModel.value = next
    expect(captured.api!.pagination.value).toEqual(next)
  })
})
