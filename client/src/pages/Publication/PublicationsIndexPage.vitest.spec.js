import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { config, flushPromises, mount } from "@vue/test-utils"
import { createMockClient } from "mock-apollo-client"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import PublicationsIndexPage from "./PublicationsIndexPage.vue"

import { describe, expect, it, vi } from "vitest"
installQuasarPlugin()

describe("publications page mount", () => {
  const mockClient = createMockClient()

  const getPubsHandler = vi.fn()
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

  const factory = () => mount(PublicationsIndexPage, {
    global: {
      plugins: config.global.plugins,
      provide: {
        ...config.global.provide,
        [ApolloClients]: { default: mockClient },
      }
    }
  })

  it("mounts without errors", async () => {
    const wrapper = factory()
    expect(wrapper).toBeTruthy()
    await flushPromises()
    expect(getPubsHandler).toHaveBeenCalledWith({ page: 1 })
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(4)
  })
})
