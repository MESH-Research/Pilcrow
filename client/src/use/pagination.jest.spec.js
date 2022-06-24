import { mount } from "vue-composable-tester"
import { createMockClient } from "mock-apollo-client"
import { usePagination } from "./pagination"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { provide } from "vue"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import flushPromises from "flush-promises"
import { isRef } from "vue"

describe("usePagination composable", () => {
  const mockClient = createMockClient({
    defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
  })
  const mountComposable = async (options) => {
    const { result } = mount(() => usePagination(GET_PUBLICATIONS, options), {
      provider: () => {
        provide(DefaultApolloClient, mockClient)
      },
    })
    await flushPromises()
    return result
  }

  const queryMock = jest.fn()
  mockClient.setRequestHandler(GET_PUBLICATIONS, queryMock)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  test("data functionality", async () => {
    const returnData = [
      ...Array(5)
        .fill(0)
        .map((_, v) => ({ id: v, name: `Pub ${v}`, home_page_content: "" })),
    ]

    const mockResponseData = (data = []) => ({
      data: {
        publications: {
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: data.length,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
          data,
        },
      },
    })

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
    queryMock.mockResolvedValue({
      data: {
        publications: {
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 0,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
          data: [],
        },
      },
    })

    await mountComposable({ variables: { page: 2 } })

    expect(queryMock).toHaveBeenCalledWith(expect.objectContaining({ page: 2 }))
  })

  test("pagination component binds", async () => {
    queryMock.mockResolvedValue({
      data: {
        publications: {
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 0,
            currentPage: 1,
            lastPage: 4,
            perPage: 10,
          },
          data: [],
        },
      },
    })

    const result = await mountComposable()

    expect(result.binds).toEqual({ modelValue: 1, min: 1, max: 4 })
  })
})
