import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import UsersIndexPage from "./UsersIndex.vue"
import {
  GetUsersDocument,
  type GetUsersQuery
} from "src/graphql/generated/graphql"
import { describe, expect, it, test, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn()
  })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("User Index page mount", () => {
  const wrapperFactory = () => mount(UsersIndexPage)

  it("mounts without errors", () => {
    expect(wrapperFactory()).toBeTruthy()
  })
  test("users are populated on the page", async () => {
    const mockUsersResponse: { data: GetUsersQuery } = {
      data: {
        users: {
          data: [
            {
              id: "1",
              name: "test1",
              email: "test1@msu.edu",
              username: "test1"
            },
            {
              id: "2",
              name: "test2",
              email: "test2@msu.edu",
              username: "test2"
            }
          ],
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 2,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: 2
          }
        }
      }
    }
    const handler = mockClient
      .getRequestHandler(GetUsersDocument)
      .mockResolvedValue(mockUsersResponse)

    const wrapper = wrapperFactory()
    await flushPromises()
    expect(handler).toHaveBeenCalled()

    const rows = wrapper.findAll("tbody tr")
    expect(rows).toHaveLength(2)

    //TODO: Validate router.push on click
  })
})
