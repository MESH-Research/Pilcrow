import { mount } from "vue-composable-tester"
import { createMockClient } from "app/test/vitest/utils"
import { usePagination, type PaginationOptions } from "./pagination"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { provide } from "vue"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import type { GetPublicationsQuery } from "src/graphql/generated/graphql"
import { flushPromises } from "@vue/test-utils"
import { isRef } from "vue"

import { describe, expect, test, vi, beforeEach } from "vitest"

describe("usePagination composable", () => {
  const mockClient = createMockClient()

  const mountComposable = async (options?: PaginationOptions) => {
    const { result } = mount(() => usePagination(GET_PUBLICATIONS, options), {
      provider: () => {
        provide(DefaultApolloClient, mockClient)
      }
    })
    await flushPromises()
    return result
  }

  const queryMock = vi.fn()
  mockClient.setRequestHandler(GET_PUBLICATIONS, queryMock)

  beforeEach(() => {
    vi.resetAllMocks()
  })

  type PublicationData = GetPublicationsQuery["publications"]["data"][number]

  const mockResponseData = (
    data: PublicationData[] = [],
    paginatorOverrides: Partial<
      GetPublicationsQuery["publications"]["paginatorInfo"]
    > = {}
  ): { data: GetPublicationsQuery } => ({
    data: {
      publications: {
        paginatorInfo: {
          __typename: "PaginatorInfo",
          count: data.length,
          currentPage: 1,
          lastPage: 1,
          perPage: 10,
          ...paginatorOverrides
        },
        data
      }
    }
  })

  test("data functionality", async () => {
    const returnData: PublicationData[] = Array(5)
      .fill(0)
      .map((_, v) => ({
        id: String(v),
        name: `Pub ${v}`,
        home_page_content: ""
      }))

    queryMock.mockResolvedValue(mockResponseData(returnData))
    const result = await mountComposable()

    expect(queryMock).toHaveBeenCalled()
    expect(isRef(result.data)).toBe(true)
    expect(result.data.value).toHaveLength(5)

    //Resolve with empty data array.
    queryMock.mockResolvedValue(mockResponseData())

    //Update page to trigger a new query
    result.updatePage(2)
    await flushPromises()

    expect(result.data.value).toHaveLength(0)
  })

  test("can override variables", async () => {
    queryMock.mockResolvedValue(mockResponseData())

    await mountComposable({ variables: { page: 2 } })

    expect(queryMock).toHaveBeenCalledWith(expect.objectContaining({ page: 2 }))
  })

  test("pagination component binds", async () => {
    queryMock.mockResolvedValue(mockResponseData([], { lastPage: 4 }))

    const result = await mountComposable()

    expect(result.binds).toEqual({ modelValue: 1, min: 1, max: 4 })
  })
})
