import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount, flushPromises } from "@vue/test-utils"
import { createMockClient } from "test/vitest/apolloClient"
import { GET_USERS } from "../../graphql/queries"
import UsersIndexPage from "./UsersIndex.vue"

import { describe, expect, it, test, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}))
installQuasarPlugin()
const wrapperFactory = (mocks) => {
  const mockClient = createMockClient()

  mocks?.forEach((mock) => {
    mockClient.setRequestHandler(...mock)
  })

  return mount(UsersIndexPage, {
    global: {
      provide: {
        [ApolloClients]: { default: mockClient },
      },
    },
  })
}
describe("User Index page mount", () => {
  it("mounts without errors", () => {
    expect(wrapperFactory([])).toBeTruthy()
  })
  test("users are populated on the page", async () => {
    const getUserHandler = vi.fn().mockResolvedValue({
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
    const wrapper = wrapperFactory([[GET_USERS, getUserHandler]])
    await flushPromises()
    expect(getUserHandler).toHaveBeenCalledWith({ page: 1 })

    const list = wrapper.findComponent({ ref: "user_list_basic" })
    expect(list.findAllComponents({ name: "q-item" })).toHaveLength(2)

    //TODO: Validate router.push on click
  })
})
