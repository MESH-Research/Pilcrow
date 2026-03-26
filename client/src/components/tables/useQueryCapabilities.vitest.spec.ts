import { describe, expect, it } from "vitest"
import { useQueryCapabilities } from "./useQueryCapabilities"
import { parse } from "graphql"

function makeQuery(variables: string): ReturnType<typeof parse> {
  const varList = variables
    ? `(${variables
        .split(",")
        .map((v) => `$${v.trim()}: String`)
        .join(", ")})`
    : ""
  return parse(`query Test${varList} { test { id } }`)
}

describe("useQueryCapabilities", () => {
  it("detects searchable when query has search variable", () => {
    const { searchable } = useQueryCapabilities(makeQuery("search"))
    expect(searchable.value).toBe(true)
  })

  it("is not searchable without search variable", () => {
    const { searchable } = useQueryCapabilities(makeQuery("page, first"))
    expect(searchable.value).toBe(false)
  })

  it("enables pagination when query has page variable", () => {
    const { enablePagination } = useQueryCapabilities(makeQuery("page"))
    expect(enablePagination.value).toBe(true)
  })

  it("disables pagination without page variable", () => {
    const { enablePagination } = useQueryCapabilities(makeQuery(""))
    expect(enablePagination.value).toBe(false)
  })

  it("provides rowsPerPageOptions when query has first variable", () => {
    const { rowsPerPageOptions } = useQueryCapabilities(makeQuery("first"))
    expect(rowsPerPageOptions.value).toEqual([10, 25, 50, 100])
  })

  it("provides empty rowsPerPageOptions without first variable", () => {
    const { rowsPerPageOptions } = useQueryCapabilities(makeQuery("page"))
    expect(rowsPerPageOptions.value).toEqual([])
  })

  it("detects all capabilities together", () => {
    const { searchable, enablePagination, rowsPerPageOptions } =
      useQueryCapabilities(makeQuery("page, first, search"))
    expect(searchable.value).toBe(true)
    expect(enablePagination.value).toBe(true)
    expect(rowsPerPageOptions.value).toEqual([10, 25, 50, 100])
  })
})
