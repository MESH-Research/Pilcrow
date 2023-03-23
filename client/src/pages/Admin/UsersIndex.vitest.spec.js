import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { GET_USERS } from "../../graphql/queries"
import UsersIndexPage from "./UsersIndex.vue"

jest.mock("vue-router", () => ({
  useRouter: () => ({
    push: jest.fn(),
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
    const getUserHandler = jest.fn().mockResolvedValue({
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
