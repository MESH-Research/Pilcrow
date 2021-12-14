import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import { DefaultApolloClient } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import UsersIndexPage from "./UsersIndex.vue"
import { GET_USERS } from "../../graphql/queries"
import Vue from "vue"

import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

const wrapperFactory = (mocks) => {
  const apolloProvider = {}
  const mockClient = createMockClient()
  apolloProvider[DefaultApolloClient] = mockClient

  mocks?.forEach((mock) => {
    mockClient.setRequestHandler(...mock)
  })

  return mountQuasar(UsersIndexPage, {
    quasar: {
      components,
    },
    //  plugins: [VueCompositionAPI],
    mount: {
      provide: apolloProvider,
      type: "full",
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
            { id: 1, name: "test1", email: "test1@msu.edu", username: "test1" },
            { id: 2, name: "test2", email: "test2@msu.edu", username: "test2" },
          ],
          paginatorInfo: {
            count: 2,
            currentPage: 1,
            perPage: 10,
            lastPage: 10,
          },
        },
      },
    })
    const wrapper = wrapperFactory([[GET_USERS, getUserHandler]])
    expect(getUserHandler).toBeCalledWith({ page: 1 })
    await Vue.nextTick()
    const list = wrapper.findComponent({ ref: "user_list_basic" })
    expect(list.findAllComponents({ name: "q-item" })).toHaveLength(2)
  })
})
