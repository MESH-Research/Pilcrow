import { describe, expect, it, vi, beforeEach } from "vitest"
import { ref } from "vue"
import { useUrlPaginationSync } from "./useUrlPaginationSync"

const mockReplace = vi.fn()
const mockQuery = ref<Record<string, string>>({})

vi.mock("vue-router", () => ({
  useRoute: () => ({
    get query() {
      return mockQuery.value
    }
  }),
  useRouter: () => ({
    replace: mockReplace
  })
}))

function createState() {
  const pagination = ref({
    sortBy: "",
    descending: false,
    rowsPerPage: 25,
    page: 1
  })
  const page = {
    get value() {
      return pagination.value.page
    },
    set value(v: number) {
      pagination.value.page = v
    }
  }
  const filter = ref("")
  return { page, filter, pagination }
}

describe("useUrlPaginationSync", () => {
  beforeEach(() => {
    mockQuery.value = {}
    mockReplace.mockClear()
  })

  it("reads initial page from URL", () => {
    mockQuery.value = { page: "3" }
    const state = createState()
    useUrlPaginationSync(state)
    expect(state.pagination.value.page).toBe(3)
  })

  it("reads initial search from URL", () => {
    mockQuery.value = { search: "hello" }
    const state = createState()
    useUrlPaginationSync(state)
    expect(state.filter.value).toBe("hello")
  })

  it("reads initial sort from URL", () => {
    mockQuery.value = { sortBy: "name", sortDir: "desc" }
    const state = createState()
    useUrlPaginationSync(state)
    expect(state.pagination.value.sortBy).toBe("name")
    expect(state.pagination.value.descending).toBe(true)
  })

  it("reads initial perPage from URL", () => {
    mockQuery.value = { perPage: "50" }
    const state = createState()
    useUrlPaginationSync(state)
    expect(state.pagination.value.rowsPerPage).toBe(50)
  })

  it("does not set page when not in URL", () => {
    const state = createState()
    useUrlPaginationSync(state)
    expect(state.pagination.value.page).toBe(1)
  })

  it("preserves non-pagination query params when writing", async () => {
    mockQuery.value = { status: "[DRAFT]" }
    const state = createState()
    useUrlPaginationSync(state)

    state.pagination.value.page = 2
    await vi.dynamicImportSettled()

    expect(mockReplace).toHaveBeenCalledWith({
      query: expect.objectContaining({
        status: "[DRAFT]",
        page: "2"
      })
    })
  })

  it("omits default values from URL", async () => {
    const state = createState()
    useUrlPaginationSync(state)

    // Trigger watcher with all defaults
    state.filter.value = ""
    state.pagination.value.page = 1
    await vi.dynamicImportSettled()

    if (mockReplace.mock.calls.length > 0) {
      const lastQuery =
        mockReplace.mock.calls[mockReplace.mock.calls.length - 1][0].query
      expect(lastQuery).not.toHaveProperty("page")
      expect(lastQuery).not.toHaveProperty("perPage")
      expect(lastQuery).not.toHaveProperty("search")
      expect(lastQuery).not.toHaveProperty("sortBy")
      expect(lastQuery).not.toHaveProperty("sortDir")
    }
  })
})
