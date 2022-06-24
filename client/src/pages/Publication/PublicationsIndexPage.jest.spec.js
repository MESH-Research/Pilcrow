import PublicationsIndexPage from "./PublicationsIndexPage.vue"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import flushPromises from "flush-promises"

installQuasarPlugin()
describe("publications page mount", () => {
  const mockClient = createMockClient()

  const getPubsHandler = jest.fn()
  mockClient.setRequestHandler(GET_PUBLICATIONS, getPubsHandler)

  getPubsHandler.mockResolvedValue({
    data: {
      publications: {
        data: [
          { id: "1", name: "Sample Jest Publication 1", home_page_content: "" },
          { id: "2", name: "Sample Jest Publication 2", home_page_content: "" },
          { id: "3", name: "Sample Jest Publication 3", home_page_content: "" },
          { id: "4", name: "Sample Jest Publication 4", home_page_content: "" },
        ],
        paginatorInfo: {
          __typename: "PaginatorInfo",
          count: 4,
          currentPage: 1,
          lastPage: 1,
          perPage: 10,
        },
      },
    },
  })

  const wrapper = mount(PublicationsIndexPage, {
    global: {
      provide: {
        [ApolloClients]: { default: mockClient },
      },
      mocks: {
        $t: (t) => t,
      },
    },
  })

  it("mounts without errors", async () => {
    await flushPromises()
    expect(wrapper).toBeTruthy()
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(4)
    expect(getPubsHandler).toBeCalledWith({ page: 1 })
  })
})
