import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { flushPromises, mount } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import PublicationsIndexPage from "../../pages/(main)/publication/index.vue"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
const mockClient = installApolloClient()

describe("publications page mount", () => {
  const handler = mockClient
    .getRequestHandler(GET_PUBLICATIONS)
    .mockResolvedValue({
      data: {
        publications: {
          data: [
            {
              id: "1",
              name: "Sample Jest Publication 1",
              home_page_content: ""
            },
            {
              id: "2",
              name: "Sample Jest Publication 2",
              home_page_content: ""
            },
            {
              id: "3",
              name: "Sample Jest Publication 3",
              home_page_content: ""
            },
            {
              id: "4",
              name: "Sample Jest Publication 4",
              home_page_content: ""
            }
          ],
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 4,
            currentPage: 1,
            lastPage: 1,
            perPage: 10
          }
        }
      }
    })

  const factory = () => mount(PublicationsIndexPage)

  it("mounts without errors", async () => {
    const wrapper = factory()
    expect(wrapper).toBeTruthy()
    await flushPromises()
    expect(handler).toHaveBeenCalledWith({ page: 1 })
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(4)
  })
})
