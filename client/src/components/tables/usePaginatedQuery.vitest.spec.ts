import { installQuasarPlugin, installApolloClient } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent, h, type Ref } from "vue"
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
})
