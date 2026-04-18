import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import PublicationIndexPage from "./PublicationIndexPage.vue"
import { Notify } from "quasar"
import {
  GetAdminPublicationsDocument,
  type GetAdminPublicationsQuery
} from "src/graphql/generated/graphql"
import { beforeEach, describe, expect, it, test, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn()
  }),
  useRoute: () => ({
    query: {}
  })
}))

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

describe("publications page mount", () => {
  const makeWrapper = () =>
    mount(PublicationIndexPage, {
      global: { stubs: ["router-link"] }
    })

  beforeEach(async () => {
    vi.resetAllMocks()
  })

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("all existing publications appear within the table", async () => {
    const mockPublicationsResponse: { data: GetAdminPublicationsQuery } = {
      data: {
        publications: {
          data: [
            {
              id: "1",
              name: "Sample Publication 1",
              created_at: "2026-01-01T00:00:00.000000Z"
            },
            {
              id: "2",
              name: "Sample Publication 2",
              created_at: "2026-01-02T00:00:00.000000Z"
            },
            {
              id: "3",
              name: "Sample Publication 3",
              created_at: "2026-01-03T00:00:00.000000Z"
            },
            {
              id: "4",
              name: "Sample Publication 4",
              created_at: "2026-01-04T00:00:00.000000Z"
            }
          ],
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 4,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: 4
          }
        }
      }
    }
    mockClient
      .getRequestHandler(GetAdminPublicationsDocument)
      .mockResolvedValue(mockPublicationsResponse)
    const wrapper = makeWrapper()
    await flushPromises()

    const rows = wrapper.findAll("tbody tr")
    expect(rows).toHaveLength(4)
  })
})
