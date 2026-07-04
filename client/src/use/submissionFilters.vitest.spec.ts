import { describe, expect, it, vi, beforeEach } from "vitest"
import { ref, nextTick } from "vue"

const query = ref<Record<string, string>>({})
const replace = vi.fn()
vi.mock("vue-router", () => ({
  useRoute: () => ({
    get query() {
      return query.value
    }
  }),
  useRouter: () => ({ replace })
}))

import {
  useSubmissionFilters,
  parseList,
  formatList
} from "./submissionFilters"

const STATUS = ["A", "B", "C"]
const ROLES = ["x", "y"]

describe("parseList", () => {
  it("parses a bracketed list", () => {
    expect(parseList("[A,B]")).toEqual(["A", "B"])
  })
  it("parses a bare value", () => {
    expect(parseList("A")).toEqual(["A"])
  })
  it("takes the first entry of an array value", () => {
    expect(parseList(["[A,B]", "ignored"])).toEqual(["A", "B"])
  })
  it("returns empty for undefined or empty input", () => {
    expect(parseList(undefined)).toEqual([])
    expect(parseList("[]")).toEqual([])
  })
})

describe("formatList", () => {
  it("wraps values in brackets", () => {
    expect(formatList(["A", "B"])).toBe("[A,B]")
  })
})

describe("useSubmissionFilters", () => {
  beforeEach(() => {
    query.value = {}
    replace.mockClear()
  })

  it("prefills the default sets when enabled and the query is empty", () => {
    const { statusFilter, roleFilter, publicationFilter } =
      useSubmissionFilters({
        defaultStatuses: STATUS,
        defaultRoles: ROLES,
        prefillDefaults: true
      })
    expect(statusFilter.value).toEqual(STATUS)
    expect(roleFilter.value).toEqual(ROLES)
    expect(publicationFilter.value).toBeNull()
  })

  it("starts empty when prefill is disabled", () => {
    const { statusFilter, roleFilter } = useSubmissionFilters({
      defaultStatuses: STATUS,
      defaultRoles: ROLES
    })
    expect(statusFilter.value).toEqual([])
    expect(roleFilter.value).toEqual([])
  })

  it("hydrates filters from the query string", () => {
    query.value = { status: "[A,B]", roles: "[x]", publication: "7" }
    const { statusFilter, roleFilter, publicationFilter } =
      useSubmissionFilters({
        defaultStatuses: STATUS,
        defaultRoles: ROLES,
        prefillDefaults: true
      })
    expect(statusFilter.value).toEqual(["A", "B"])
    expect(roleFilter.value).toEqual(["x"])
    expect(publicationFilter.value).toBe("7")
  })

  it("writes non-default selections to the URL and resets the table to page 1", async () => {
    const tableRef = ref({ page: 5 })
    const { statusFilter } = useSubmissionFilters({
      defaultStatuses: STATUS,
      defaultRoles: ROLES,
      tableRef,
      prefillDefaults: true
    })
    statusFilter.value = ["A"]
    await nextTick()
    expect(tableRef.value.page).toBe(1)
    expect(replace).toHaveBeenCalledWith({ query: { status: "[A]" } })
  })

  it("drops a filter from the URL once it returns to the default set", async () => {
    query.value = { status: "[A]" }
    const { statusFilter } = useSubmissionFilters({
      defaultStatuses: STATUS,
      defaultRoles: ROLES,
      prefillDefaults: true
    })
    statusFilter.value = [...STATUS]
    await nextTick()
    expect(replace).toHaveBeenCalledWith({ query: {} })
  })

  it("clears the publication param when set to null", async () => {
    query.value = { publication: "7" }
    const { publicationFilter } = useSubmissionFilters({
      defaultStatuses: STATUS,
      defaultRoles: ROLES,
      prefillDefaults: true
    })
    publicationFilter.value = null
    await nextTick()
    expect(replace).toHaveBeenCalledWith({ query: {} })
  })
})
