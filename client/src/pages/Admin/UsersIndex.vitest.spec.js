import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { GET_USERS } from "../../graphql/queries"
import UsersIndexPage from "./UsersIndex.vue"

import { describe, expect, it, test, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("User Index page mount", () => {
  const wrapperFactory = () => mount(UsersIndexPage)

  it("mounts without errors", () => {
    expect(wrapperFactory([])).toBeTruthy()
  })
  test("users are populated on the page", async () => {
    const handler = mockClient.getRequestHandler(GET_USERS).mockResolvedValue({
      data: {
        userSearch: {
          data: [
            {
              id: "1",
              name: "test1",
              email: "test1@msu.edu",
              username: "test1",
            },
            {
              id: "2",
              name: "test2",
              email: "test2@msu.edu",
              username: "test2",
            },
          ],
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 2,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
        },
      },
    })

    const wrapper = wrapperFactory()
    await flushPromises()
    expect(handler).toHaveBeenCalledWith({ page: 1 })

    const list = wrapper.findComponent({ ref: "user_list_basic" })
    expect(list.findAllComponents({ name: "q-item" })).toHaveLength(2)

    //TODO: Validate router.push on click
  })
})
