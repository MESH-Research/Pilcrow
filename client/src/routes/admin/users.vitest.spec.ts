import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import UsersIndexPage from "./users.vue"
import {
  GetUsersDocument,
  type GetUsersQuery
} from "src/graphql/generated/graphql"
import { describe, expect, it, test, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn()
  }),
  useRoute: () => ({
    query: {}
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
              username: "test1",
              avatar_color: "blue",
              created_at: "2026-01-01T00:00:00Z",
              email_verified_at: null
            },
            {
              id: "2",
              name: "test2",
              email: "test2@msu.edu",
              username: "test2",
              avatar_color: "green",
              created_at: "2026-01-02T00:00:00Z",
              email_verified_at: null
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
  })
})
