import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
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
              home_page_content: ""
            },
            {
              id: "2",
              name: "Sample Publication 2",
              home_page_content: ""
            },
            {
              id: "3",
              name: "Sample Publication 3",
              home_page_content: ""
            },
            {
              id: "4",
              name: "Sample Publication 4",
              home_page_content: ""
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
